<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SERVICE_RATES = [
        'Cuci Kering' => ['unit' => 'kg', 'price' => 6000],
        'Cuci Setrika' => ['unit' => 'kg', 'price' => 8000],
        'Setrika Saja' => ['unit' => 'kg', 'price' => 5000],
        'Bed Cover' => ['unit' => 'pcs', 'price' => 25000],
    ];

    public function up(): void
    {
        $missingServices = array_diff(
            array_keys(self::SERVICE_RATES),
            DB::table('services')->whereIn('name', array_keys(self::SERVICE_RATES))->pluck('name')->all(),
        );

        if ($missingServices !== []) {
            throw new RuntimeException('Migration ongkir dihentikan karena layanan katalog belum lengkap: '.implode(', ', $missingServices));
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping_fee', 12, 2)->nullable()->after('delivery_fee');
        });

        DB::table('services')->whereNotIn('name', array_keys(self::SERVICE_RATES))
            ->update(['is_active' => false]);

        foreach (self::SERVICE_RATES as $name => $details) {
            $updated = DB::table('services')->where('name', $name)->update([
                'unit' => $details['unit'],
                'price_per_unit' => $details['price'],
                'is_active' => true,
                'updated_at' => now(),
            ]);

            if ($updated === 0 && ! DB::table('services')->where('name', $name)->exists()) {
                throw new RuntimeException("Layanan {$name} belum dibuat oleh migration katalog sebelumnya.");
            }
        }
    }

    public function down(): void
    {
        if (DB::table('orders')->whereNotNull('shipping_fee')->exists()) {
            throw new RuntimeException('Rollback ditolak karena order sudah memiliki snapshot ongkir. Tidak ada data order yang dihapus.');
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_fee');
        });

        // Service tariff/status are intentionally retained; reverting them could
        // reactivate legacy services or overwrite prices changed by an admin.
    }
};
