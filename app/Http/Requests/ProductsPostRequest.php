<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductsPostRequest extends FormRequest
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
            'name' => 'required|string|unique:products,name|max:50',
            'type' => 'required|string|in:hot dish,cold dish,drink, dessert',
            'description' => 'required|string|max:255',
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'price' => 'required|numeric|gt:0',
        ];
    }
}
