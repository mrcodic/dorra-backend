<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mockup_generation_jobs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('mockup_id')
                ->constrained('mockups')
                ->cascadeOnDelete();

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'completed_with_errors',
                'failed',
                'cancelled',
            ])->default('pending')->index();

            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('mockup_id');
            $table->index(['mockup_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mockup_generation_jobs');
    }
};
