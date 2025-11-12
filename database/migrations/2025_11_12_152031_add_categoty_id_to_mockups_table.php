<?php

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
        Schema::table('mockups', function (Blueprint $table) {
            $table->after('name',function (Blueprint $table){
                $table->foreignIdFor(\App\Models\Category::class)
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mockups', function (Blueprint $table) {
            $table->dropForeign('mockups_category_id_foreign');
            $table->dropColumn('category_id');

        });
    }
};
