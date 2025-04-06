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
        Schema::table('users', function (Blueprint $table) {
            $table->after('country_code_id',function (Blueprint $table) {
                $table->boolean('is_mobile_notifications_enabled')->default(true);
                $table->boolean('is_email_notifications_enabled')->default(true);
            });


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_mobile_notifications_enabled');
            $table->dropColumn('is_email_notifications_enabled');

        });
    }
};
