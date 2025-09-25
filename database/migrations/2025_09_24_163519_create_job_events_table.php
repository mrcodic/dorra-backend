<?php

use App\Models\Admin;
use App\Models\JobTicket;
use App\Models\Operator;
use App\Models\Station;
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
        Schema::create('job_events', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Station::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(JobTicket::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Operator::class)->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_events');
    }
};
