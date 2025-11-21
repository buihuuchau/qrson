<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    public function postLogin(Request $request)
    {
        try {
            $acceptFields = [
                'phone',
                'password'
            ];
            $credentials = Arr::only(request()->all(), $acceptFields);

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'status_code' => 401,
                    'messages' => 'Số điện thoại hoặc mật khẩu không đúng.',
                ], 401);
            }

            $user = auth()->user();

            if ($user->role == 'admin') {
                return response()->json([
                    'status' => false,
                    'status_code' => 403,
                    'messages' => 'Bạn không phải là User. Hãy dùng tài khoản User để đăng nhập lại.',
                ], 403);
            }

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Đăng nhập thành công.',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'user' => $user,
                ]
            ], 200);
        } catch (\Throwable $th) {
            Log::error('LoginController postLogin error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'messages' => 'Lỗi hệ thống.',
            ], 500);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Đăng xuất thành công.',
            ], 200);
        } catch (\Throwable $th) {
            Log::error('LoginController logout error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'status_code' => 500,
                'messages' => 'Lỗi hệ thống.',
            ], 500);
        }
    }
}
