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
        $table->foreignId('parent_id')->after('sequence')->nullable()->index()->constrained()->cascadeOnDelete();
        $table->foreignIdFor(JobTicket::class)->nullable()->index()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('station_statuses', function (Blueprint $table) {
            //
        });
    }
};
