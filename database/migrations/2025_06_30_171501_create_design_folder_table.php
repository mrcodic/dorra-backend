<?php

use App\Models\Folder;
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
        Schema::create('design_folder', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('design_id')->references('id')->on('designs')->onDelete('cascade');
            $table->foreignIdFor(Folder::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_folder');
    }
};
