<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer()) {
            return $order->customer_id === $user->id;
        }

        if ($user->isCourier()) {
            return $order->assignments()->where('courier_id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer() && $order->customer_id === $user->id) {
            return $order->canBeCancelled();
        }

        return false;
    }

    public function pay(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer() && $order->customer_id === $user->id) {
            return $order->canAcceptPayment();
        }

        return false;
    }

    public function updateStage(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
