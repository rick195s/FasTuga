<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = Auth::guard('api')->user();

        // authenticated users cannot create customers
        if ($this->type == "C" && !$user) {
            return true;
        }

        // only managers can create employees
        return  $user && $user->isManager();
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
            'type' => [
                'string', 'in:C,EC,ED,EM',
            ],
            'photo' => ['image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'phone' => [
                Rule::requiredIf($this->type == 'C'),
                'numeric', 'digits:9', 'unique:customers'
            ],
            'nif' => [
                Rule::requiredIf($this->type == 'C'),
                'numeric', 'digits:9', 'unique:customers'
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
            if (!Auth::guard('api')->user()) {
                $this->merge([
                    'type' => 'C',
                ]);
            } else {
                $this->merge([
                    'type' => 'ED',
                ]);
            }
        }
    }
}
