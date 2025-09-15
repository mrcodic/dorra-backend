<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('design_specifications')) {
            Schema::create('design_specifications', function (Blueprint $table) {
                $table->id();

                // Prefer foreignUuid to match the UUID FK type
                $table->foreignUuid('design_id')
                    ->constrained('designs',)
                    ->cascadeOnDelete();

                $table->unsignedBigInteger('product_spec_id');
                $table->foreign('product_spec_id')
                    ->references('id')
                    ->on('product_specifications')
                    ->cascadeOnDelete();

                $table->unsignedBigInteger('option_id')->nullable();
                $table->foreign('option_id')
                    ->references('id')
                    ->on('product_specification_options')
                    ->nullOnDelete();

                $table->timestamps();

                $table->index(['design_id', 'product_spec_id']);
            });
        } else {
            Schema::table('design_specifications', function (Blueprint $table) {
                if (!Schema::hasColumn('design_specifications', 'design_id')) return;

            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('design_specifications');
    }
};

