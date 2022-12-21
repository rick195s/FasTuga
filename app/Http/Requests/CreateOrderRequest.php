<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Services\Payment;
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

    protected function prepareForValidation()
    {
        $this->merge([
            'payment_type' => Str::upper($this->payment_type),
        ]);
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

            (new Payment($validator, $this->payment_type, $this->payment_reference, $this->total))
                ->validatePayment();
        });
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
