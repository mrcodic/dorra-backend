<?php

use App\Models\Mockup;
use App\Models\Template;
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
        Schema::create('mockup_template', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('template_id')->constrained('templates')->cascadeOnDelete();
            $table->foreignIdFor(Mockup::class)->constrained()->cascadeOnDelete();
            $table->json('positions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mockup_template');
    }
};
