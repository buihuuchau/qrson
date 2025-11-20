<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class documentAddRequest extends FormRequest
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
            'shipment_id' => 'required',
            'document_id' => 'required',
            'total' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'shipment_id.required' => 'Shipment ID không được để trống.',
            'document_id.required' => 'Số chứng từ không được để trống.',
            'total.required' => 'Số lượng mã của Số chứng từ không được để trống.',
            'total.numeric'  => 'Số lượng mã của Số chứng từ phải là số.',
            'total.min'      => 'Số lượng mã của Số chứng từ phải lớn hơn hoặc bằng 0.',
        ];
    }
}
