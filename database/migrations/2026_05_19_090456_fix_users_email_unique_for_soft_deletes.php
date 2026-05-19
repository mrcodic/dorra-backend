<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
        });

        DB::statement('
            ALTER TABLE users
            ADD COLUMN email_unique_active
                VARCHAR(255) GENERATED ALWAYS AS (
                    IF(deleted_at IS NULL, email, NULL)
                ) VIRTUAL UNIQUE
        ');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP COLUMN email_unique_active');

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
