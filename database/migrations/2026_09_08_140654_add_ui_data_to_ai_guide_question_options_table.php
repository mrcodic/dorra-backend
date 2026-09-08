<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_guide_question_options', function (Blueprint $table) {
            $table->json('ui_data')
                ->nullable()
                ->after('prompt_value');
        });
    }

    public function down(): void
    {
        Schema::table('ai_guide_question_options', function (Blueprint $table) {
            $table->dropColumn('ui_data');
        });
    }
};
