<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // authorized in the UserPolicy
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
            'name' => 'string|max:255',
            'password' => [
                'confirmed', Rules\Password::defaults(),
                Rule::prohibitedIf(auth()->user()->id != $this->user->id),
            ],
            'type' => [
                // 'C' not allowed because customers can be created only in register
                'string', 'in:EC,ED,EM',
                Rule::prohibitedIf(!auth()->user()->isManager() || auth()->user()->id === $this->user->id),

            ],
            'phone' => [
                'numeric', 'digits:9', 'unique:customers,phone,' . $this->user->customer?->id
            ],
            'nif' => [
                'numeric', 'digits:9', 'unique:customers,nif,' . $this->user->customer?->id
            ],
            'photo' => ['image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'default_payment_type' => 'required_if:default_payment_id,true|string|in:VISA,PAYPAL,MBWAY',
            'default_payment_id' => 'required_if:default_payment_type,true|string|max:50'
        ];
    }


    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        // checks user current password
        // before making changes
        $validator->after(function ($validator) {
            if (
                $this->password && !Hash::check($this->old_password, $this->user->password)
            ) {
                $validator->errors()->add('old_password', 'Your old password is incorrect.');
            }
        });
    }
}
