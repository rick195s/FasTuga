<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // enums are only available in PHP 8.1+
            'type' => [
                'string', 'in:C,EC,ED,EM',

                // if user is authenticate just the managers can fill
                // the 'type' field
                Rule::prohibitedIf(Auth::user() && !Auth::user()->is_manager)
            ],
            'phone' => [
                Rule::requiredIf($this->type == 'C'),
                'string', 'min:9', 'max:15', 'unique:customers'
            ],
            'nif' => [
                Rule::requiredIf($this->type == 'C'),
                'string', 'digits:9', 'unique:customers'
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if (empty($this->type)) {
            $this->merge([
                'type' => 'C',
            ]);
        }
    }
}
