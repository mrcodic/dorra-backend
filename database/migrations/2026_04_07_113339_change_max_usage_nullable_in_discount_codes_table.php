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
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->unsignedInteger('max_usage')->nullable()->change();
            $table->date('expired_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->unsignedInteger('max_usage')->nullable(false)->change();
            $table->date('expired_at')->nullable(false)->change();
        });
    }
};
