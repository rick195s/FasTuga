<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'phone',
        'points',
        'nif',
        'default_payment_type',
        'default_payment_reference',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];



    // relation customer 1:n order
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // relation customer 1:1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function defaultPaymentId()
    {
        if ($this->default_payment_id == null) {
            switch ($this->default_payment_type) {
                case 'MBWAY':
                    return $this->phone;

                case 'PAYPAL':
                    return $this->email;

                default:
                    return $this->default_payment_id;
            }
        }

        return $this->default_payment_id;
    }
}
