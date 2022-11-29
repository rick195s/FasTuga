<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UpdateDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->id == $this->driver->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $driver = $this->driver;
        return [
            'password' => ['confirmed', Rules\Password::defaults()],
            'phone' => ['string', 'min:9', 'max:15', Rule::unique('drivers')->ignore($driver->id)],
            'license_plate' => [
                'regex:/[\d\w]{2}[-][\d\w]{2}[-][\d\w]{2}|[\d\w]{2}[ ][\d\w]{2}[ ][\d\w]{2}/',
                'string', 'size:8 ', Rule::unique('drivers')->ignore($driver->id),
            ],
            'photo' => ['image', 'mimes:jpeg,png,jpg', 'max:2048'],
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
                $this->password && !Hash::check($this->old_password, $this->driver->user->password)
            ) {
                $validator->errors()->add('old_password', 'Your old password is incorrect.');
            }
        });
    }
}
