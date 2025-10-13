<?php

use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->after('is_already_printed',function ($table) {
                $table->foreignIdFor(Inventory::class)
                    ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            });

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_inventory_id_foreign');
            $table->dropIndex('orders_inventory_id_foreign');
            $table->dropColumn('inventory_id');
        });
    }
};
