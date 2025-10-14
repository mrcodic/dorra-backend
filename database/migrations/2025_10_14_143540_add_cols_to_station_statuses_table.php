<?php

use App\Models\JobTicket;
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
        Schema::table('station_statuses', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->after('sequence')
                ->nullable()
                ->constrained('station_statuses')
                ->cascadeOnDelete();


            $table->foreignIdFor(JobTicket::class)
                ->nullable()
                ->after('parent_id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('station_statuses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropConstrainedForeignId('job_ticket_id');
        });
    }
};
