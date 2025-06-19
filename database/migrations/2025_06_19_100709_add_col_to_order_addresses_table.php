<?php

use App\Models\ShippingAddress;
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
        Schema::table('order_addresses', function (Blueprint $table) {
            $table->after('state',function (Blueprint $table) {
                $table->foreignIdFor(ShippingAddress::class)->nullable()->constrained()->nullOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_addresses', function (Blueprint $table) {
            $table->dropForeign('order_addresses_shipping_address_foreign');
            $table->dropIndex('order_addresses_shipping_address_foreign');
            $table->dropColumn('shipping_address_id');
        });
    }
};
