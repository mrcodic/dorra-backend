<?php

use App\Models\Team;
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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Team::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('design_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->tinyInteger('status')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
