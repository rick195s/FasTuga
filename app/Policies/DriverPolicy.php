<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DriverPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Driver $driver)
    {
        return $user->id == $driver->user->id;
    }

    public function orders(User $user)
    {
        return $user->driver;
    }

    public function statistics(User $user)
    {
        return $user->driver;
    }
}
