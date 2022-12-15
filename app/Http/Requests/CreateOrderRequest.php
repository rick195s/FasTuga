<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "payment_type" => "required|string|in:MBWAY,PAYPAL,VISA",
            "payment_reference" => "required|string",
            "points_used_to_pay" => "multiple_of:10",
            "items.*.product_id" => "required|exists:products,id",
            "items.*.quantity" => "required|integer|min:1|max:10",
        ];
    }

    public function withValidator($validator)
    {
        // checks user current password
        // before making changes
        $validator->after(function ($validator) {
            switch (strtoupper($this->payment_type)) {
                case 'MBWAY':
                    $this->validateMBWAY($validator);
                    break;
                case 'PAYPAL':
                    $this->validatePAYPAL($validator);
                    break;
                case 'VISA':
                    $this->validateVISA($validator);
                    break;
            }
        });
    }

    public function validateMBWAY($validator)
    {
        if (
            Str::length(trim($this->payment_reference)) != 9 ||
            Str::startsWith($this->payment_reference, '0')
        ) {
            $validator->errors()->add('payment_reference', 'Payment reference invalid for MBWAY.');
        };
    }

    public function validatePAYPAL($validator)
    {
        // validate if $this->payment_reference is a valid email with validator
        if (!$validator->validateEmail("payment_reference", $this->payment_reference, [])) {
            $validator->errors()->add('payment_reference', 'Payment reference invalid for PAYPAL.');
        };
    }

    public function validateVISA($validator)
    {
        if (
            Str::length(trim($this->payment_reference)) != 16 ||
            Str::startsWith($this->payment_reference, '0')
        ) {
            $validator->errors()->add('payment_reference', 'Payment reference invalid for VISA.');
        };
    }
}
