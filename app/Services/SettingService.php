<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;

class SettingService
{
    public function current(): Setting
    {
        return Setting::firstOrCreate([], ['laundry_price_per_kg' => 8000, 'pickup_fee' => 5000, 'delivery_fee' => 5000]);
    }

    public function update(array $data, User $admin): Setting
    {
        $setting = $this->current();
        $setting->update([...$data, 'updated_by' => $admin->id]);

        return $setting;
    }
}
