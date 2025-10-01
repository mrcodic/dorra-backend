<?php

use App\Enums\JobTicket\StatusEnum;
use App\Models\OrderItem;
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
        Schema::create('job_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignIdFor(OrderItem::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Station::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('current_status_id')->nullable()->constrained('station_statuses')->cascadeOnDelete();
            $table->json('specs')->nullable();
            $table->string('hold_reason')->nullable();
            $table->tinyInteger('priority')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->tinyInteger('status')->default(StatusEnum::PENDING->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_tickets');
    }
};
