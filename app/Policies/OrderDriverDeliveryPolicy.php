<?php

namespace App\Policies;

use App\Models\OrderDriverDelivery;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderDriverDeliveryPolicy
{
    use HandlesAuthorization;

    public function update(User $user, OrderDriverDelivery $orderDriverDelivery)
    {
        return $orderDriverDelivery->deliveredBy &&
            $user->id === $orderDriverDelivery->deliveredBy->user->id;
    }
}
