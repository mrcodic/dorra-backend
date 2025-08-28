<?php

use App\Models\ProductSpecification;
use App\Models\ProductSpecificationOption;
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
        Schema::table('order_item_specs', function (Blueprint $table) {
            $table->after('option_price',function (Blueprint $table) {
                $table->foreignIdFor(ProductSpecification::class)
                    ->nullable()->constrained()->nullOnDelete();
                $table->foreignIdFor(ProductSpecificationOption::class, 'spec_option_id')->nullable()
                    ->constrained('product_specification_options')
                    ->nullOnDelete();
            });

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_item_specs', function (Blueprint $table) {
            $table->dropForeign('order_item_specs_spec_option_id_foreign');
            $table->dropForeign('order_item_specs_spec_option_id_option_id_foreign');
        });
    }
};
