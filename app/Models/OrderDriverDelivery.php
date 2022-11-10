<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// FasTuga Driver integration
class OrderDriverDelivery extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'delivery_location',
        'tax_fee',
        'delivery_started_at',
        'delivery_ended_at',
    ];

    protected $table = 'orders_driver_delivery';

    public $timestamps = false;

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

    // relation order_driver_delivery 1:1 order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // relation order_driver_delivery 1:1 driver
    public function deliveredBy()
    {
        return $this->order->deliveredBy();
    }
}
