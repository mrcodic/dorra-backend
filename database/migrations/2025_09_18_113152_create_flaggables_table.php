<?php

use App\Models\Flag;
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
        Schema::create('flaggables', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Flag::class)->constrained()->cascadeOnDelete();
            $table->string('flaggable_id');
            $table->string('flaggable_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flaggables');
    }
};
