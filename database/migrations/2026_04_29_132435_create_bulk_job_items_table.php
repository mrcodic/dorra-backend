<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_job_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bulk_job_id')
                ->constrained('bulk_jobs')
                ->cascadeOnDelete();

            $table->foreignId('template_id')
                ->constrained('templates')
                ->cascadeOnDelete();

            $table->string('color', 7);
            $table->string('side', 10);

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
            ])->default('pending')->index();

            $table->string('output_path', 500)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempts')->default(0);

            $table->timestamps();

            $table->index('bulk_job_id');
            $table->index('template_id');
            $table->index(['bulk_job_id', 'status']);
            $table->index(['template_id', 'status']);
            $table->index(['color', 'side']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_job_items');
    }
};
