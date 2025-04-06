<?php

use App\Models\NotificationType;
use App\Models\User;
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
        Schema::create('notification_type_user', function (Blueprint $table) {
            $table->foreignIdFor(NotificationType::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class )->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->primary(['notification_type_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_type_user');
    }
};
