<?php

use App\Models\Zone;
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
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->after('guest_id',function (Blueprint $table){
                $table->foreignIdFor(Zone::class)->nullable()->constrained()->nullOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->dropForeign('shipping_addresses_zone_id_foreign');
            $table->dropIndex('shipping_addresses_zone_id_foreign');
            $table->dropColumn('zone_id');
        });
    }
};
