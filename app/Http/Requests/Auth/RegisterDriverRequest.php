<?php

namespace App\Http\Requests\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

// fastuga driver integration
class RegisterDriverRequest extends FormRequest
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
            'phone' => ['required', 'string', 'min:9', 'max:15', 'unique:drivers'],
            'license_plate' => [
                'required', 'regex:/[\d\w]{2}[-][\d\w]{2}[-][\d\w]{2}|[\d\w]{2}[ ][\d\w]{2}[ ][\d\w]{2}/',
                'string', 'size:8 ', 'unique:drivers'
            ],
        ];
    }
}