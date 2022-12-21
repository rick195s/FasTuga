<?php

namespace App\Services;

use Illuminate\Validation\Validator;
use Illuminate\Support\Str;

class Payment
{

    public function __construct(Validator $validator, $payment_type, $payment_reference, $total = null)
    {
        $this->payment_type = $payment_type;
        $this->payment_reference = $payment_reference;
        $this->validator = $validator;

        $this->total = $total;
    }

    public function validatePayment()
    {
        switch (strtoupper($this->payment_type)) {
            case 'MBWAY':
                return $this->validateMBWAY();
            case 'PAYPAL':
                return $this->validatePAYPAL();
            case 'VISA':
                return $this->validateVISA();
            default:
                return false;
        }
    }
    public function validateMBWAY()
    {
        if (
            Str::length(trim($this->payment_reference)) != 9 ||
            !Str::startsWith($this->payment_reference, '9')
        ) {
            $this->validator->errors()->add('payment_reference', "Payment reference must have length 9 and start with '9'");
            $this->validator->errors()->add('default_payment_reference', "Payment reference must have length 9 and start with '9'");
        };

        if ($this->total && $this->total > 10) {
            $this->validator->errors()->add('payment_type', 'Payment type MBWAY has a maximum limit of 10€');
        }
    }

    public function validatePAYPAL()
    {
        if (
            !$this->validator->validateEmail("payment_reference", $this->payment_reference, [])
            || !Str::endsWith($this->payment_reference, ['.com', '.pt'])
        ) {
            $this->validator->errors()->add('payment_reference', "Payment reference must be email or end with '.com' or '.pt'");
            $this->validator->errors()->add('default_payment_reference', "Payment reference must be email or end with '.com' or '.pt'");
        };

        if ($this->total && $this->total > 50) {
            $this->validator->errors()->add('payment_type', 'Payment type PAYPAL has a maximum limit of 50€');
        }
    }

    public function validateVISA()
    {
        if (
            Str::length(trim($this->payment_reference)) != 16 ||
            !Str::startsWith($this->payment_reference, '4')
        ) {
            $this->validator->errors()->add('payment_reference', "Payment reference must have length 16 and start with '4'");
            $this->validator->errors()->add('default_payment_reference', "Payment reference must have length 16 and start with '4'");
        };

        if ($this->total && $this->total > 200) {
            $this->validator->errors()->add('payment_type', 'Payment type VISA has a maximum limit of 200€');
        }
    }
}
