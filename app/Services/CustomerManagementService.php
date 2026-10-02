<?php

namespace App\Services;

use App\Models\User;
use Exception;

class CustomerManagementService
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function update(User $customer, array $data, User $admin): User
    {
        if (! $customer->isCustomer()) {
            throw new Exception('Akun yang dipilih bukan customer.');
        }
        $customer->update($data);
        $this->activityLog->record($admin, "Memperbarui customer {$customer->email}");

        return $customer;
    }

    public function toggle(User $customer, User $admin): User
    {
        if (! $customer->isCustomer()) {
            throw new Exception('Akun yang dipilih bukan customer.');
        }
        $customer->update(['is_active' => ! $customer->is_active]);
        $this->activityLog->record($admin, ($customer->is_active ? 'Mengaktifkan' : 'Menonaktifkan')." customer {$customer->email}");

        return $customer;
    }
}
