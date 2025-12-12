<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LogAndDbController extends Controller
{
    private function getEnvAccountList(): array
    {
        $env = env('LOG_VIEW_ACCOUNTS', '');

        // Nếu rỗng hoặc chỉ toàn khoảng trắng → fallback
        if (empty(trim($env))) {
            $env = "admin:password";
        }

        // Chuyển string thành array
        // Ví dụ: "a:1,b:2" → [ "a" => "1", "b" => "2" ]
        $pairs = array_map('trim', explode(',', $env));
        $validList = [];

        foreach ($pairs as $p) {
            if (str_contains($p, ':')) {
                [$acc, $pass] = explode(':', $p, 2);
                $validList[trim($acc)] = trim($pass);
            }
        }

        return $validList;
    }

    private function checkAccountPasswordENV($account, $password, $ip)
    {
        // Lấy danh sách account từ ENV (đã fallback admin:password)
        $validList = $this->getEnvAccountList();

        // Cấu hình từ ENV
        $maxFail = intval(env('LOG_VIEW_MAX_FAIL') ?: 100);     // số lần sai tối đa
        $lockTTL = intval(env('LOG_VIEW_LOCK_TTL') ?: 86400);   // TTL khóa (1 ngày)

        // Account không tồn tại
        if (!isset($validList[$account])) {
            return [
                'status' => false,
                'locked' => false,
                'attempts_left' => false,
                'message' => 'ACCOUNT NOT FOUND',
            ];
        }

        // Cache key theo từng account
        $cacheKey = "login_account_fail_{$account}";
        $failCount = Cache::get($cacheKey, 0);

        // Account bị khóa
        if ($failCount >= $maxFail) {
            return [
                'status' => false,
                'locked' => true,
                'attempts_left' => 0,
                'message' => 'ACCOUNT LOCKED DUE TO TOO MANY FAILED ATTEMPTS'
            ];
        }

        // Password đúng → reset fail
        if ($validList[$account] === $password) {
            Cache::forget($cacheKey);

            return [
                'status' => true,
                'locked' => false,
            ];
        }

        // ======== PASSWORD SAI ========
        $failCount++;
        Cache::put($cacheKey, $failCount, $lockTTL); // auto reset sau TTL

        $attemptsLeft = max(0, $maxFail - $failCount);

        Log::warning('API WRONG LOGIN', [
            'ip' => $ip,
            'datetime' => now()->toDateTimeString(),
            'account' => $account,
            'fail_count' => $failCount,
        ]);

        return [
            'status' => false,
            'locked' => false,
            'attempts_left' => $attemptsLeft,
            'message' => 'WRONG PASSWORD'
        ];
    }

    public function log(Request $request)
    {
        try {
            // Lấy param cần thiết
            $params = Arr::only($request->all(), ['account', 'password', 'name']);

            // Validate thiếu
            if (empty($params['account']) || empty($params['password'])) {
                return response()->json([
                    'status' => false,
                    'status_code' => 400,
                    'message' => 'post, account ???, password ???'
                ], 400);
            }

            // ================= CHECK LOGIN HERE =================
            $check = $this->checkAccountPasswordENV($params['account'], $params['password'], $request->ip());

            // Nếu đăng nhập sai → KHÔNG log
            if (!$check['status']) {

                // Tài khoản bị khóa → cũng KHÔNG log
                if (!empty($check['locked'])) {
                    return response()->json([
                        'status' => false,
                        'status_code' => 423,
                        'message' => 'ACCOUNT LOCKED',
                        'attempts_left' => $check['attempts_left']
                    ], 423);
                }

                // Sai password hoặc account không tồn tại → KHÔNG log
                return response()->json([
                    'status' => false,
                    'status_code' => 401,
                    'message' => $check['message'],
                    'attempts_left' => $check['attempts_left'],
                ], 401);
            }

            // ====================================================
            // ĐĂNG NHẬP ĐÚNG → CHỈ LÚC NÀY MỚI GHI LOG CHỨC NĂNG
            // ====================================================
            Log::info('API LOG FUNCTION CALLED', [
                'ip' => $request->ip(),
                'datetime' => now()->toDateTimeString(),
                'action' => 'VIEW SYSTEM LOG',
                'user' => $params['account'],
                'file' => $params['name'] ?? null,
                'user_agent' => $request->header('User-Agent'),
            ]);

            // ================== SHOW FILE LOG ==================
            $logDir = storage_path('logs');

            // Nếu có name → trả nội dung file
            if (!empty($params['name'])) {

                $filePath = $logDir . '/' . $params['name'];

                if (!File::exists($filePath)) {
                    return response()->json([
                        'status' => false,
                        'status_code' => 404,
                        'message' => "Log file {$params['name']} not found!"
                    ], 404);
                }

                // Đọc file và explode theo dòng
                $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'data' => [
                        'file_name' => $params['name'],
                        'file_count_line' => count($lines),
                        'file_data' => $lines
                    ]
                ], 200);
            }

            // Không có name → trả danh sách file
            $files = File::files($logDir);
            $fileNames = array_map(fn($file) => $file->getFilename(), $files);

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'post, account, password, name ???',
                'data' => [
                    'file_name' => $fileNames,
                ]
            ], 200);
        } catch (\Throwable $th) {

            // Exception → cũng KHÔNG log đăng nhập, chỉ log lỗi hệ thống
            Log::error('API LOG EXCEPTION', [
                'ip' => $request->ip(),
                'datetime' => now()->toDateTimeString(),
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'SERVER ERROR. GO CHECK THE LOGS.'
            ], 500);
        }
    }

    public function database(Request $request)
    {
        try {
            // Lấy param cần thiết
            $params = Arr::only(
                $request->all(),
                [
                    'account',
                    'password',
                    'help',
                    'cmd',
                    'tableName',
                    'where',
                    'orWhere',
                    'or',
                    'orderBy',
                    'get',
                    'value'
                ]
            );

            // Validate thiếu
            if (empty($params['account']) || empty($params['password'])) {
                return response()->json([
                    'status' => false,
                    'status_code' => 400,
                    'message' => 'post, account ???, password ???'
                ], 400);
            }

            if (empty($params['cmd']) && empty($params['help'])) {
                return response()->json([
                    'status' => false,
                    'status_code' => 400,
                    'message' => 'post, cmd ??? OR help ???'
                ], 400);
            }

            // ================= CHECK LOGIN HERE =================
            $check = $this->checkAccountPasswordENV($params['account'], $params['password'], $request->ip());

            // Nếu đăng nhập sai → KHÔNG log
            if (!$check['status']) {

                // Tài khoản bị khóa → cũng KHÔNG log
                if (!empty($check['locked'])) {
                    return response()->json([
                        'status' => false,
                        'status_code' => 423,
                        'message' => 'ACCOUNT LOCKED',
                        'attempts_left' => $check['attempts_left']
                    ], 423);
                }

                // Sai password hoặc account không tồn tại → KHÔNG log
                return response()->json([
                    'status' => false,
                    'status_code' => 401,
                    'message' => $check['message'],
                    'attempts_left' => $check['attempts_left'],
                ], 401);
            }

            // ====================================================
            // ĐĂNG NHẬP ĐÚNG → CHỈ LÚC NÀY MỚI GHI LOG CHỨC NĂNG
            // ====================================================
            Log::info('API SUPER ADMIN FUNCTION CALLED', [
                'ip' => $request->ip(),
                'datetime' => now()->toDateTimeString(),
                'action' => 'SUPER ADMIN',
                'user' => $params['account'],
                'user_agent' => $request->header('User-Agent'),
            ]);

            if (isset($params['help'])) {
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'OPTION',
                    'data' => [
                        'param' => [
                            'cmd' => ['allTable', 'allColumn', 'select', 'insert', 'update', 'delete'],
                            'tableName' => 'table name',
                            'where' => "expamle param in <>----<name:Tên User 1,phone:0000000001>",
                            'orWhere' => "expamle param in <>----<role:user>",
                            'or' => "expamle param in <>----<name:Tên User 2,name:Tên User 5>",
                            'orderBy' => "created_at:desc",
                            'get' => ['first'],
                        ]
                    ]
                ], 200);
            }

            switch ($params['cmd']) {
                case 'allTable':
                    $tables = DB::select('SHOW TABLES');
                    return response()->json([
                        'status' => true,
                        'status_code' => 200,
                        'message' => 'SHOW ALL TABLES NAME',
                        'data' => [
                            'table_name' => $tables
                        ]
                    ], 200);
                case 'allColumn':
                    $paramTableName = $params['tableName'] ?? null;

                    // Kiểm tra table name
                    if (!$paramTableName) {
                        return response()->json([
                            'status' => false,
                            'status_code' => 400,
                            'message' => 'TABLE NAME REQUIRED'
                        ]);
                    }

                    // Lấy danh sách cột dùng SQL trực tiếp
                    $columns = DB::select("SHOW COLUMNS FROM `$paramTableName`");

                    return response()->json([
                        'status' => true,
                        'status_code' => 200,
                        'message' => 'SHOW ALL COLUMNS OF TABLE',
                        'data' => [
                            'table_name' => $paramTableName,
                            'columns'    => $columns
                        ]
                    ], 200);
                case 'select':
                    $paramTableName = $params['tableName'] ?? null;
                    $paramWhere     = $params['where'] ?? null;     // các phần tử AND, nhóm này AND với nhóm khác
                    $paramOrWhere   = $params['orWhere'] ?? null;   // các phần tử AND, nhóm này OR với nhóm khác
                    $paramOr        = $params['or'] ?? null;        // các phần tử OR, nhóm này AND với nhóm khác (ví dụ)
                    $orderByParam   = $params['orderBy'] ?? null;

                    if (!$paramTableName) {
                        return response()->json([
                            'status'      => false,
                            'status_code' => 400,
                            'message'     => 'TABLE NAME REQUIRED'
                        ]);
                    }

                    $query = DB::table($paramTableName);

                    // ===== WHERE (AND giữa các phần tử, nhóm AND với nhóm khác)
                    if ($paramWhere) {
                        $arrWhere = explode(',', $paramWhere);
                        $query->where(function ($q) use ($arrWhere) {
                            foreach ($arrWhere as $item) {
                                [$key, $value] = explode(':', $item, 2);
                                $q->where($key, $value); // AND giữa các phần tử
                            }
                        });
                    }

                    // ===== OR WHERE (AND giữa các phần tử, nhóm OR với nhóm khác)
                    if ($paramOrWhere) {
                        $arrOrWhere = explode(',', $paramOrWhere);
                        $query->orWhere(function ($q) use ($arrOrWhere) {
                            foreach ($arrOrWhere as $item) {
                                [$key, $value] = explode(':', $item, 2);
                                $q->where($key, $value); // AND giữa các phần tử trong nhóm
                            }
                        });
                    }

                    // ===== OR riêng (OR giữa các phần tử, nhóm này AND với nhóm khác)
                    if ($paramOr) {
                        $arrOr = explode(',', $paramOr);
                        $query->where(function ($q) use ($arrOr) {
                            foreach ($arrOr as $index => $item) {
                                [$key, $value] = explode(':', $item, 2);
                                if ($index === 0) {
                                    $q->where($key, $value); // bắt đầu nhóm
                                } else {
                                    $q->orWhere($key, $value); // OR giữa các phần tử trong nhóm
                                }
                            }
                        });
                    }

                    // ===== ORDER BY
                    if ($orderByParam) {
                        $arrOrder = explode(',', $orderByParam);
                        foreach ($arrOrder as $item) {
                            [$column, $direction] = explode(':', $item, 2);
                            $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
                            $query->orderBy($column, $direction);
                        }
                    }

                    // ===== SQL debug
                    $sql = $query->toSql();

                    // ===== Lấy dữ liệu
                    $data = (!empty($params['get']) && $params['get'] === 'first')
                        ? $query->first()
                        : $query->get();

                    return response()->json([
                        'status'      => true,
                        'status_code' => 200,
                        'sql'         => $sql,
                        'data'        => $data
                    ], 200);
                case 'insert':
                    $paramTableName = $params['tableName'] ?? null;
                    $paramValue     = $params['value'] ?? null;

                    // Kiểm tra table name và dữ liệu
                    if (!$paramTableName || !$paramValue) {
                        return response()->json([
                            'status'      => false,
                            'status_code' => 400,
                            'message'     => 'TABLE NAME AND VALUE REQUIRED'
                        ]);
                    }

                    // Chuyển chuỗi value thành mảng key => value
                    $arrPairs = explode(',', $paramValue);
                    $insertData = [];
                    foreach ($arrPairs as $item) {
                        [$key, $value] = explode(':', $item, 2);
                        $insertData[$key] = $value;
                    }

                    $query = DB::table($paramTableName)->insert($insertData);
                    $sql = DB::table($paramTableName)->toSql();

                    return response()->json([
                        'status'      => true,
                        'status_code' => 200,
                        'message'     => 'INSERT SUCCESS',
                        'sql'         => $sql,
                        'data'        => $insertData
                    ]);
                    break;
                case 'update':
                    $paramTableName = $params['tableName'] ?? null;
                    $paramValue     = $params['value'] ?? null;

                    // Kiểm tra table name và dữ liệu
                    if (!$paramTableName || !$paramValue) {
                        return response()->json([
                            'status'      => false,
                            'status_code' => 400,
                            'message'     => 'TABLE NAME AND VALUE REQUIRED'
                        ]);
                    }

                    // Chuyển chuỗi value thành mảng key => value
                    $arrPairs = explode(',', $paramValue);
                    $updateData = [];
                    $whereData  = [];

                    foreach ($arrPairs as $index => $item) {
                        [$key, $value] = explode(':', $item, 2);
                        if ($index === 0) {
                            // Trường đầu tiên dùng để WHERE
                            $whereData[$key] = $value;
                        } else {
                            $updateData[$key] = $value;
                        }
                    }

                    if (empty($updateData)) {
                        return response()->json([
                            'status'      => false,
                            'status_code' => 400,
                            'message'     => 'NO FIELDS TO UPDATE'
                        ]);
                    }

                    // Thực hiện update
                    $query = DB::table($paramTableName)->where($whereData)->update($updateData);
                    $sql = DB::table($paramTableName)->where($whereData)->toSql();

                    return response()->json([
                        'status'      => true,
                        'status_code' => 200,
                        'message'     => 'UPDATE SUCCESS',
                        'sql'         => $sql,
                        'where'       => $whereData,
                        'update'        => $updateData
                    ]);
                    break;
                case 'delete':
                    $paramTableName = $params['tableName'] ?? null;
                    $paramValue     = $params['value'] ?? null;

                    // Kiểm tra table name và dữ liệu
                    if (!$paramTableName || !$paramValue) {
                        return response()->json([
                            'status'      => false,
                            'status_code' => 400,
                            'message'     => 'TABLE NAME AND VALUE REQUIRED'
                        ]);
                    }

                    // Chuyển chuỗi value thành mảng key => value
                    $arrPairs = explode(',', $paramValue);
                    $whereData = [];

                    // Chỉ lấy trường đầu tiên làm WHERE
                    if (isset($arrPairs[0])) {
                        [$key, $value] = explode(':', $arrPairs[0], 2);
                        $whereData[$key] = $value;
                    } else {
                        return response()->json([
                            'status'      => false,
                            'status_code' => 400,
                            'message'     => 'NO WHERE FIELD PROVIDED'
                        ]);
                    }

                    // Thực hiện delete
                    $deleted = DB::table($paramTableName)->where($whereData)->delete();
                    $sql = DB::table($paramTableName)->where($whereData)->toSql();

                    return response()->json([
                        'status'      => true,
                        'status_code' => 200,
                        'message'     => 'DELETE SUCCESS',
                        'sql'         => $sql,
                        'where'       => $whereData,
                        'deleted'     => $deleted
                    ]);
                    break;
                default:
                    return response()->json([
                        'status'      => false,
                        'status_code' => 400,
                        'message'     => 'NO SUCH COMMAND'
                    ]);
                    break;
            }
        } catch (\Throwable $th) {

            // Exception → cũng KHÔNG log đăng nhập, chỉ log lỗi hệ thống
            Log::error('API SUPER ADMIN EXCEPTION', [
                'ip' => $request->ip(),
                'datetime' => now()->toDateTimeString(),
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
