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
        Schema::create('mockup_side_settings', function (Blueprint $table) {
            Schema::create('mockup_side_settings', function (Blueprint $table) {
                $table->id();

                $table->foreignIdFor(\App\Models\Mockup::class)
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('side', 20);
                $table->boolean('is_active')->default(true);

                $table->json('warp_points')->nullable();
                $table->json('render_presets')->nullable();

                $table->timestamps();

                $table->unique(['mockup_id', 'side']);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mockup_side_settings');
    }
};
