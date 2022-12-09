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

    public function start_delivery(User $user, OrderDriverDelivery $orderDriverDelivery)
    {
        return $this->update($user, $orderDriverDelivery) &&
            !$orderDriverDelivery->delivery_started_at;
    }
}
