<?php

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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('bundle_id')->nullable()->after('discount_amount')->constrained('bundles')->nullOnDelete();
            $table->foreignId('bundle_item_id')->nullable()->after('bundle_id')->constrained('bundle_items')->nullOnDelete();

            $table->uuid('bundle_group_key')->nullable()->after('bundle_item_id');
            $table->string('bundle_role')->nullable()->after('bundle_group_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign('cart_items_bundle_id_foreign');
            $table->dropForeign('cart_items_bundle_item_id_foreign');
            $table->dropColumn('bundle_id');
            $table->dropColumn('bundle_item_id');
            $table->dropColumn('bundle_group_key');
            $table->dropColumn('bundle_role');
        });
    }
};
