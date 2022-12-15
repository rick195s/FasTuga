<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
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
            "items" => "required|array",
            "items.*.product_id" => "required|exists:products,id",
            "items.*.quantity" => "required|integer|min:1|max:50",
            "items.*.notes" => "string|max:200",
            "total" => "prohibited",
        ];
    }

    public function validated($key = null, $default = null)
    {
        return array_merge(parent::validated($key, $default), [
            "total" => $this->total,
        ]);
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $this->merge([
                'total' => $this->getTotal(),
            ]);

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
            !Str::startsWith($this->payment_reference, '9')
        ) {
            $validator->errors()->add('payment_reference', 'Payment reference must have length 9 and start with "9"');
        };

        if ($this->total > 10) {
            $validator->errors()->add('payment_type', 'Payment type MBWAY has a maximum limit of 10€');
        }
    }

    public function validatePAYPAL($validator)
    {
        if (
            !$validator->validateEmail("payment_reference", $this->payment_reference, [])
            || !Str::endsWith($this->payment_reference, ['.com', '.pt'])
        ) {
            $validator->errors()->add('payment_reference', 'Payment reference must be email or end with ".com" or ".pt"');
        };

        if ($this->total > 50) {
            $validator->errors()->add('payment_type', 'Payment type PAYPAL has a maximum limit of 50€');
        }
    }

    public function validateVISA($validator)
    {
        if (
            Str::length(trim($this->payment_reference)) != 16 ||
            !Str::startsWith($this->payment_reference, '4')
        ) {
            $validator->errors()->add('payment_reference', 'Payment reference must have length 16 and start with "4"');
        };

        if ($this->total > 200) {
            $validator->errors()->add('payment_type', 'Payment type VISA has a maximum limit of 200€');
        }
    }

    public function getTotal()
    {
        $items = collect($this->items)->pluck('quantity', 'product_id');

        $prices = Product::whereIn('id', $items->keys())->pluck('price', 'id');

        $total = 0;
        foreach ($prices as $id => $price) {
            $total += $price * (int) $items->get($id);
        }

        return (float) $total;
    }
}
