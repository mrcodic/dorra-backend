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
        Schema::table('templates', function (Blueprint $table) {
            $table->integer("dpi")->nullable()->after("unit")->default(300);
            $table->decimal('width')->nullable()->change();
            $table->decimal('height')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn("dpi");
            $table->integer('width')->change();
            $table->integer('height')->change();
        });
    }
};
