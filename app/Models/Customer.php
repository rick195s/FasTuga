<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';


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
}
