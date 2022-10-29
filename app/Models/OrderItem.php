<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;


    // relation order 1:n order_item
    public function order()
    {
        return $this->belongsTo(Order::class);
    }


    // relation product 1:n order_item
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // relation user 1:n order_item
    public function preparatedBy()
    {
        return $this->belongsTo(User::class);
    }
}
