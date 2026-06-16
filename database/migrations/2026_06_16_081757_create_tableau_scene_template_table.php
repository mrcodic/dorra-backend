<?php

use App\Models\TableauScene;
use App\Models\Template;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tableau_scene_template', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('template_id')
                ->constrained('templates')
                ->cascadeOnDelete();

            $table->foreignId('tableau_scene_id')
                ->constrained('tableau_scenes')
                ->cascadeOnDelete();

            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->unique(['template_id', 'tableau_scene_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('tableau_scene_template');
    }
};
