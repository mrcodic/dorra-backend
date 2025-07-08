<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToTeamsDesignsFolders extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('designs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('designs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
