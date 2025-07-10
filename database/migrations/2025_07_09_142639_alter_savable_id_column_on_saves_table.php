<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('saves', function (Blueprint $table) {
            $table->dropIndex(['savable_type', 'savable_id']);
            $table->string('savable_id')->change();
            $table->index(['savable_type', 'savable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('saves', function (Blueprint $table) {

            $table->dropIndex(['savable_type', 'savable_id']);
            $table->unsignedBigInteger('savable_id')->change();
            $table->index(['savable_type', 'savable_id']);
        });
    }
};
