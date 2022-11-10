<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'blocked',
        'photo_url'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    // check if user is customer
    public function is_customer()
    {
        return $this->type == 'C';
    }

    // check if user is manager
    public function is_manager()
    {
        return $this->type == 'EM';
    }

    // check if user is employee chef
    public function is_employee_chef()
    {
        return $this->type == 'EC';
    }

    // check if user is employee delivery
    public function is_employee_delivery()
    {
        return $this->type == 'ED';
    }

    // relation user 1:n order
    public function orders()
    {
        return $this->hasMany(Order::class, 'delivered_by');
    }

    // relation user 1:n order
    public function order_items()
    {
        return $this->hasMany(OrderItem::class, 'preparation_by');
    }

    // relation customer 1:1 user
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    // relation driver 1:1 user
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }
}
