<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_number',
        'status',
        'customer_id',
        'total_price',
        'total_paid',
        'total_paid_with_points',
        'points_gained',
        'points_used_to_pay',
        'payment_type',
        'payment_reference',
        'date',
        'delivered_by'
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


    // relation customers 1:n orders
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    // relation order 1:n orderItems
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // relation order 1:1 order_driver_delivery
    public function orderDriverDelivery()
    {
        return $this->hasOne(OrderDriverDelivery::class);
    }

    // relation user 1:n order
    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by', 'id');
    }
}
