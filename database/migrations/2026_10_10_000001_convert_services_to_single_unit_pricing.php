<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CANONICAL_SERVICES = [
        'Cuci Kering' => 'kg',
        'Cuci Setrika' => 'kg',
        'Setrika Saja' => 'kg',
        'Bed Cover' => 'pcs',
    ];

    public function up(): void
    {
        $ordersWithoutItems = DB::table('orders')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('order_items.id')
            ->count();
        $ordersWithMultipleItems = DB::table('order_items')
            ->select('order_id')
            ->groupBy('order_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($ordersWithoutItems > 0 || $ordersWithMultipleItems > 0) {
            throw new RuntimeException(
                'Migrasi tarif layanan dihentikan: ditemukan order tanpa item atau dengan lebih dari satu item. '
                .'Periksa dan tentukan pemetaan data terlebih dahulu; migrasi tidak menghapus atau menebak data.'
            );
        }

        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('price_per_kg', 'price_per_unit');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->string('unit', 8)->default('kg')->after('description');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('service_name_snapshot')->nullable()->after('service_id');
            $table->string('unit', 8)->default('kg')->after('service_name_snapshot');
            $table->decimal('estimated_quantity', 10, 2)->nullable()->after('quantity');
            $table->decimal('actual_quantity', 10, 2)->nullable()->after('estimated_quantity');
        });

        $items = DB::table('order_items')->get();
        foreach ($items as $item) {
            $serviceName = DB::table('services')->where('id', $item->service_id)->value('name');
            $estimated = DB::table('orders')->where('id', $item->order_id)->value('estimated_weight');
            $actual = DB::table('orders')->where('id', $item->order_id)->value('actual_weight');

            DB::table('order_items')->where('id', $item->id)->update([
                'service_name_snapshot' => $serviceName ?: 'Layanan',
                'unit' => 'kg',
                'estimated_quantity' => $estimated ?? $item->quantity,
                'actual_quantity' => $actual,
            ]);
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->unique('order_id');
        });

        DB::table('services')->update(['is_active' => false]);
        foreach (self::CANONICAL_SERVICES as $name => $unit) {
            $existing = DB::table('services')->where('name', $name)->first();
            if ($existing) {
                DB::table('services')->where('id', $existing->id)->update(['unit' => $unit]);
            } else {
                DB::table('services')->insert([
                    'name' => $name,
                    'description' => null,
                    'unit' => $unit,
                    'price_per_unit' => 0,
                    'estimated_hours' => 48,
                    'is_active' => false,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (DB::table('orders')->exists()) {
            throw new RuntimeException('Rollback ditolak karena order lama memakai snapshot satuan/tarif. Data order tetap dipertahankan.');
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique(['order_id']);
            $table->dropColumn(['service_name_snapshot', 'unit', 'estimated_quantity', 'actual_quantity']);
        });
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->renameColumn('price_per_unit', 'price_per_kg');
        });
    }
};
