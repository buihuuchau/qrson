<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            if (Auth()->user()->role == 'admin') {
                return redirect()->route('web.shipment.list');
            } else {
                return redirect()->route('user.scan.shipment');
            }
        }
        return view('web.auth.login');
    }

    public function postLogin(Request $request)
    {
        try {
            $acceptFields = [
                'phone',
                'password'
            ];
            $credentials = Arr::only(request()->all(), $acceptFields);

            if (Auth::attempt($credentials)) {
                if (Auth::user()['role'] == 'admin') {
                    return redirect()->route('web.shipment.list');
                } else {
                    return redirect()->route('user.scan');
                }
            } else {
                return redirect()->back()->withErrors(['login' => 'Số điện thoại hoặc mật khẩu không đúng. Vui lòng thử lại.'])->withInput();
            }
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors(['login' => 'Số điện thoại hoặc mật khẩu không đúng. Vui lòng thử lại.'])->withInput();
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('web.login');
    }
}
