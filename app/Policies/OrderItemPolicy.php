<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->isEmployeeChef();
    }


    public function update(User $user, OrderItem $orderItem)
    {
        return $user->isEmployeeChef() && $orderItem->preparation_by == $user->id &&
            $orderItem->product->type == 'hot dish' &&
            ($orderItem->status == 'W' || $orderItem->status == 'P');
    }
}
