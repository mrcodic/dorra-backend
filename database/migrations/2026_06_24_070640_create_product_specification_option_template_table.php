<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_specification_option_template', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('template_id')
                ->constrained('templates')
                ->cascadeOnDelete();
            $table->foreignId('option_id')
                ->constrained('product_specification_options')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'option_id'], 'template_spec_option_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specification_option_template');
    }
};
