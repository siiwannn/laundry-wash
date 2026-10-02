<?php

namespace App\Policies;

use App\Models\CustomerAddress;
use App\Models\User;

class CustomerAddressPolicy
{
    public function view(User $user, CustomerAddress $address): bool
    {
        return $user->isAdmin() || $address->user_id === $user->id;
    }

    public function update(User $user, CustomerAddress $address): bool
    {
        return $address->user_id === $user->id;
    }

    public function delete(User $user, CustomerAddress $address): bool
    {
        return $address->user_id === $user->id;
    }
}
