<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\userAddRequest;
use App\Http\Requests\userUpdateRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $userService;

    public function __construct(
        UserService $userService,
    ) {
        $this->userService = $userService;
    }

    public function list(Request $request)
    {
        try {
            $acceptFields = [
                'phone',
                'name',
                'role',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            if (!empty($result['phone'])) {
                $filterUser['phone'] = $result['phone'];
            }
            if (!empty($result['name'])) {
                $filterUser['whereRaw'] = 'name like "%' . $result['name'] . '%"';
            }
            if (!empty($result['role'])) {
                $filterUser['role'] = $result['role'];
            }

            $filterUser['orderBy'] = 'id';

            $filterUser['get'] = [
                'paginate' => 50,
            ];
            $users = $this->userService->filter($filterUser);
            $data['users'] = $users;
            return view('web.user.list', $data);
        } catch (\Throwable $th) {
            Log::error('Web/UserController list error: ' . $th->getMessage());
            abort(404);
        }
    }

    public function add(userAddRequest $request)
    {
        try {
            $acceptFields = [
                'phone',
                'password',
                'name',
                'role',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $filterUser = [
                'phone' => $result['phone'],
                'get' => 'first',
            ];
            $user = $this->userService->filter($filterUser);
            if (!empty($user)) {
                return back()->withErrors('Số điện thoại này đã được đăng ký.');
            }

            $valueAddUser = [
                'name' => $result['name'],
                'phone' => $result['phone'],
                'password' => bcrypt($result['password']),
                'role' => $result['role'],
            ];
            $createUser = $this->userService->create($valueAddUser);
            if ($createUser != false) {
                return back()->with('success', 'Thêm Nhân viên thành công.');
            } else {
                return back()->withErrors('Thêm Nhân viên thất bại.')->withInput();
            }
        } catch (\Throwable $th) {
            Log::error('Web/UserController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }

    public function update(userUpdateRequest $request)
    {
        try {
            $acceptFields = [
                'user_id',
                'password',
                'name',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $valueUpdateUser = [
                'name' => $result['name'],
            ];

            if (!empty($result['password'])) {
                $valueUpdateUser['password'] = bcrypt($result['password']);
            }
            $updateUser = $this->userService->update($result['user_id'], $valueUpdateUser);
            if ($updateUser != false) {
                return back()->with('success', 'Chỉnh sửa Nhân viên thành công.');
            } else {
                return back()->withErrors('Chỉnh sửa Nhân viên thất bại.')->withInput();
            }
        } catch (\Throwable $th) {
            Log::error('Web/UserController update error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $acceptFields = [
                'user_id',
            ];
            $result = Arr::only(request()->all(), $acceptFields);

            $user = $this->userService->find($result['user_id']);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'status_code' => 404,
                    'message' => 'Nhân viên không tồn tại.',
                ], 404);
            }

            $deleteUser = $this->userService->delete($user->id);
            if ($deleteUser != false) {
                return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'message' => 'Xóa Nhân viên thành công.',
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'status_code' => 409,
                    'message' => 'Xóa Nhân viên thất bại.',
                ], 409);
            }
        } catch (\Throwable $th) {
            Log::error('Web/UserController delete error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'message' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
