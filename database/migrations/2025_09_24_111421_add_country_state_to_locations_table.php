<?php

use App\Models\State;
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
        Schema::table('locations', function (Blueprint $table) {
//            $table->dropForeign('locations_state_id_foreign');
//            $table->dropColumn('state_id');
            $table->string('country')->nullable()->after('longitude');
            $table->string('state')->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
//            $table->foreignIdFor(State::class)->constrained()->restrictOnDelete();
            $table->dropColumn(['state', 'country']);
        });
    }
};
