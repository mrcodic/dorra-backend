<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_guide_question_conditions')) {
            return;
        }

        /*
         * The original condition table allowed only one row per assignment.
         * Find that UNIQUE index by column instead of assuming its exact name,
         * because earlier migrations used different index names.
         */
        $indexes = DB::select(
            "SHOW INDEX FROM ai_guide_question_conditions
             WHERE Column_name = 'ai_guide_question_assignment_id'
             AND Non_unique = 0"
        );

        foreach ($indexes as $index) {
            $keyName = (string) ($index->Key_name ?? '');

            if ($keyName === '' || strtoupper($keyName) === 'PRIMARY') {
                continue;
            }

            $safeKeyName = str_replace('`', '``', $keyName);

            DB::statement(
                "ALTER TABLE ai_guide_question_conditions DROP INDEX `{$safeKeyName}`"
            );
        }
    }

    public function down(): void
    {
        /*
         * Do not recreate the old unique index automatically.
         * Once multiple condition rows exist, recreating it would fail
         * or require deleting valid data.
         */
    }
};
