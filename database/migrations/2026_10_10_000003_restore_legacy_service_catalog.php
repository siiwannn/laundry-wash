<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CATALOG = [
        'Cuci Komplit Reguler' => ['unit' => 'kg', 'price' => 9000, 'hours' => 48],
        'Cuci Kilat Express' => ['unit' => 'kg', 'price' => 15000, 'hours' => 24],
        'Setrika Saja' => ['unit' => 'kg', 'price' => 5000, 'hours' => 24],
        'Bed Cover & Selimut Tebal' => ['unit' => 'pcs', 'price' => 20000, 'hours' => 48],
    ];

    public function up(): void
    {
        // Retain the existing Express service row (and any FK references), while
        // shortening its current catalog label to the name previously shown.
        $express = DB::table('services')->where('name', 'Cuci Kilat Express')->first();
        $expressWithDuration = DB::table('services')->where('name', 'Cuci Kilat Express (1 Hari)')->first();
        if (! $express && $expressWithDuration) {
            DB::table('services')->where('id', $expressWithDuration->id)->update([
                'name' => 'Cuci Kilat Express',
                'updated_at' => now(),
            ]);
        }

        DB::table('services')->update(['is_active' => false]);

        foreach (self::CATALOG as $name => $details) {
            $service = DB::table('services')->where('name', $name)->first();
            $attributes = [
                'description' => match ($name) {
                    'Cuci Komplit Reguler' => 'Cuci bersih, pengeringan higienis, setrika rapi, dan kemasan wangi.',
                    'Cuci Kilat Express' => 'Layanan kilat dengan estimasi selesai 12–24 jam.',
                    'Setrika Saja' => 'Setrika uap profesional tanpa proses pencucian.',
                    'Bed Cover & Selimut Tebal' => 'Layanan khusus bed cover dan selimut tebal.',
                },
                'unit' => $details['unit'],
                'price_per_unit' => $details['price'],
                'estimated_hours' => $details['hours'],
                'is_active' => true,
                'updated_at' => now(),
            ];

            if ($service) {
                DB::table('services')->where('id', $service->id)->update($attributes);
            } else {
                DB::table('services')->insert([
                    ...$attributes,
                    'name' => $name,
                    'created_at' => now(),
                ]);
            }
        }

        // If both historical Express labels existed, keep the old row but do
        // not expose it as a second selectable service.
        DB::table('services')->where('name', 'Cuci Kilat Express (1 Hari)')
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        throw new RuntimeException('Rollback otomatis ditolak agar tidak mengubah tarif layanan yang mungkin sudah disunting Admin.');
    }
};
