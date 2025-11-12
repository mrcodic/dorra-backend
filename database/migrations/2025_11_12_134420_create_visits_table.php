<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->ipAddress('ip');
            $table->unsignedInteger('hits')->default(0);
            $table->unique(['date', 'ip'], 'visits_date_ip_unique');
            $table->index('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }



};
