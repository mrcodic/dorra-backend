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
            $table->after('sequence',function ($table){
                $table->nullableMorphs('resourceable');
                $table->boolean('is_custom')->default(false);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('station_statuses', function (Blueprint $table) {
         $table->dropMorphs('resourceable');
         $table->dropColumn('is_custom');
        });
    }
};
