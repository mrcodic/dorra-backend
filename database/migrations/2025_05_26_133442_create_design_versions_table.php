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
        Schema::create('design_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('design_id')->constrained('designs')->cascadeOnDelete();
            $table->json('design_data');
            $table->unsignedInteger('version');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_versions');
    }
};
