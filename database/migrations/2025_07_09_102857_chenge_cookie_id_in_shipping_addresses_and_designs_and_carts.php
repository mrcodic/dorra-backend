<?php

use App\Models\Guest;
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
             $table->dropColumn('cookie_id');
             $table->foreignIdFor(Guest::class)->after('user_id')->nullable()->constrained()->nullOnDelete();
        });
        Schema::table('designs', function (Blueprint $table) {
             $table->dropColumn('cookie_id');
            $table->foreignIdFor(Guest::class)->after('user_id')->nullable()->constrained()->nullOnDelete();

        });
        Schema::table('carts', function (Blueprint $table) {
             $table->dropColumn('cookie_id');
            $table->foreignIdFor(Guest::class)->after('user_id')->nullable()->constrained()->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropColumn('guest_id');
            $table->string('cookie_id')->nullable();
        });

        Schema::table('designs', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropColumn('guest_id');
            $table->string('cookie_id')->nullable();
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropColumn('guest_id');
            $table->string('cookie_id')->nullable();
        });
    }

};
