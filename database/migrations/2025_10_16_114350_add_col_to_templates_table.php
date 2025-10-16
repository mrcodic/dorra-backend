<?php

use App\Models\Dimension;
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
        Schema::table('templates', function (Blueprint $table) {
            $table->foreignIdFor(Dimension::class)
            ->nullable()->constrained()
            ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropForeign('templates_dimension_id_foreign');
            $table->dropIndex('templates_dimension_id_foreign');
            $table->dropColumn('dimension_id');
        });
    }
};
