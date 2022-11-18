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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'order_local_number',
        'product_id',
        'status',
        'price',
        'preparation_by',
        'notes'
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
    public function preparationBy()
    {
        return $this->belongsTo(User::class, "preparation_by");
    }

    public function statusToString()
    {
        switch ($this->status) {
            case 'P':
                return "Preparing";
            case 'R':
                return "Ready";
            case 'W':
            default:
                return "Waiting";
        }
    }
}
