<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class codeProductAddRequest extends FormRequest
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
            'code_product_id' => 'required|string|size:27',
        ];
    }

    public function messages(): array
    {
        return [
            'shipment_id.required' => 'Shipment No không được để trống.',
            'document_id.required' => 'Số chứng từ không được để trống.',
            'code_product_id.required' => 'Mã sản phẩm không được để trống.',
            'code_product_id.size' => 'Mã sản phẩm không đúng.',
        ];
    }
}
