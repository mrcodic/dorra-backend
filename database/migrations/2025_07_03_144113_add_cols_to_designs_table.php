<?php

use App\Models\Product;
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
        Schema::table('designs', function (Blueprint $table) {
            $table->after('id', function ($table) {
                $table->string('name')->nullable();;
                $table->string('description')->nullable();
                $table->integer("height")->nullable();
                $table->integer("width")->nullable();
                $table->tinyInteger("unit")->nullable();
                $table->foreignIdFor(Product::class)->nullable()->constrained()->cascadeOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->dropColumn('height');
            $table->dropColumn('width');
            $table->dropColumn('unit');
            $table->dropForeign('product_id');
            $table->dropColumn('product_id');
        });
    }
};
