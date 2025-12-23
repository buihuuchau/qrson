<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class addDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipment_id'  => 'required',
            'document_id'  => 'required',
            'total'        => 'required|numeric|min:1',
            'codeProducts' => 'required',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $value = $this->input('codeProducts');

            $codes = [];

            if (is_array($value)) {
                $codes = $value;
            } elseif (is_string($value)) {
                $codes = array_filter(
                    array_map('trim', explode(',', $value)),
                    fn($p) => $p !== ''
                );
            } else {
                $validator->errors()->add(
                    'codeProducts',
                    'codeProducts không hợp lệ'
                );
                return;
            }

            if (count($codes) < 1) {
                $validator->errors()->add(
                    'codeProducts',
                    'Phải có ít nhất 1 mã'
                );
                return;
            }

            $onlyCodes = [];

            foreach ($codes as $index => $item) {
                if (!$this->isValidCodeYesNo($item)) {
                    $validator->errors()->add(
                        is_array($value) ? "codeProducts.$index" : 'codeProducts',
                        'Định dạng phải là <mã 27 ký tự>:yes hoặc <mã 27 ký tự>:no'
                    );
                    continue;
                }

                [$code] = explode(':', $item);
                $onlyCodes[] = $code;
            }

            if (count($onlyCodes) !== count(array_unique($onlyCodes))) {
                $validator->errors()->add(
                    'codeProducts',
                    'Danh sách mã bị trùng'
                );
            }
        });
    }


    private function isValidCodeYesNo(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9]{27}:(yes|no)$/', trim($value)) === 1;
    }

    public function messages(): array
    {
        return [
            'shipment_id.required' => 'Shipment No không được để trống.',
            'document_id.required' => 'Số chứng từ không được để trống.',
            'total.required'       => 'Số lượng mã của Số chứng từ không được để trống.',
            'total.numeric'        => 'Số lượng mã của Số chứng từ phải là số.',
            'total.min'            => 'Số lượng mã của Số chứng từ phải lớn hơn hoặc bằng 1.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'      => false,
                'status_code' => 422,
                'message'     => $validator->errors(),
            ], 422)
        );
    }
}
