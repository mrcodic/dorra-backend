<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favourite_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('favourite_id')->constrained()->cascadeOnDelete();

            $table->string('favouritable_type', 100);
            $table->string('favouritable_id', 64);
            $table->string('contextable_type', 100);
            $table->string('contextable_id', 64);

            $table->timestamps();

            $table->index(['favouritable_type', 'favouritable_id'], 'favourite_items_favouritable_idx');
            $table->index(['contextable_type', 'contextable_id'], 'favourite_items_contextable_idx');
            $table->unique([
                'favourite_id',
                'favouritable_type',
                'favouritable_id',
                'contextable_type',
                'contextable_id',
            ], 'favourite_items_identity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favourite_items');
    }
};
