<?php

namespace App\Services;

use App\Enums\CourierStatus;
use App\Models\CourierProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountProfileService
{
    /** @param array<string, mixed> $data */
    public function update(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data): void {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);

            if ($user->isCourier()) {
                CourierProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    ['status' => CourierStatus::OFFLINE],
                )->update([
                    'vehicle_type' => $data['vehicle_type'],
                    'vehicle_plate' => $data['vehicle_plate'],
                ]);
            }
        });
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->update(['password' => Hash::make($password)]);
    }
}
