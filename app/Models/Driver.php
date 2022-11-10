<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// FasTuga Driver integration
class Driver extends Model
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
        'license_plate',
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

    // relation driver 1:n ordersDriverDelivery
    public function ordersDriverDelivery()
    {
        return $this->hasManyThrough(OrderDriverDelivery::class, Order::class, 'delivered_by', 'order_id', 'user_id', 'id');
    }

    // relation driver 1:1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
