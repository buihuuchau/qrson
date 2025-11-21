<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class userUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->input('user_id') ?? $this->route('user_id');

        return [
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6|confirmed', // chỉ validate nếu có gửi
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên nhân viên không được để trống.',
            'name.max' => 'Tên nhân viên tối đa 255 ký tự.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không giống nhau.',
        ];
    }
}
