<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;


    // relation customers 1:n orders
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    // relation order 1:n order_items
    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }


    // relation user 1:n order
    public function deliveredBy()
    {
        return $this->belongsTo(User::class);
    }
}
