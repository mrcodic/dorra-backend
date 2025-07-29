<?php

use App\Models\Type;
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
        Schema::create('typeables', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Type::class)->constrained('types')->cascadeOnDelete();
            $table->string('typeable_id');
            $table->string('typeable_type');
            $table->index(['typeable_id', 'typeable_type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('typeables');
    }
};
