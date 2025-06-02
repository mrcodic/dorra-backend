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
            $table->json("description")->after("name")->nullable();
            $table->integer("height")->nullable()->after("description");
            $table->integer("width")->nullable()->after("height");
            $table->tinyInteger("unit")->after("width")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn("description");
            $table->dropColumn("height");
            $table->dropColumn("width");
            $table->dropColumn("unit");
        });
    }
};
