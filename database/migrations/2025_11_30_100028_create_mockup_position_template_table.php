<?php

use App\Models\MockupTemplate;
use App\Models\Position;
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
        Schema::create('mockup_position_template', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Position::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(MockupTemplate::class,'mockup_template_id')
                ->constrained()->cascadeOnDelete();
            $table->tinyInteger('template_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mockup_position_template');
    }
};
