<?php

use App\Models\Mockup;
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
            $table->after('total_price',function (Blueprint $table) {
                $table->boolean('linked_to_mockup')->default(false);
                $table->string('mockup_color')->nullable();
                $table->foreignIdFor(Mockup::class)->nullable()
                    ->constrained()->nullOnDelete();
                $table->json('design_mockup_area')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->dropColumn('design_mockup_area');
            $table->dropColumn('linked_to_mockup');
            $table->dropConstrainedForeignId('mockup_id');
        });
    }
};
