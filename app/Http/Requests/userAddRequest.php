<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class userAddRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string|min:10|max:10|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
            'name' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Số điện thoại nhân viên không được để trống.',
            'phone.unique' => 'Số điện thoại này đã được đăng ký.',
            'phone.min' => 'Số điện thoại phải có 10 số.',
            'phone.max' => 'Số điện thoại phải có 10 số.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.confirmed' => 'Xác nhận mật khẩu không giống nhau.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'name.required' => 'Tên nhân viên không được để trống.',
        ];
    }
}
