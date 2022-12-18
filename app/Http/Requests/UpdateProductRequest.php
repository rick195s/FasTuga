<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'name' => 'string|unique:products,name|max:50',
            'type' => 'string|in:hot dish,cold dish,drink, dessert',
            'description' => 'string|max:255',
            'photo' => ['image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'price' => 'numeric|gt:0',
        ];
    }
}
