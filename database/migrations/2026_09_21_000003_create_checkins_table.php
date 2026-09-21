<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('reported_at');
            $table->enum('source', ['button', 'manual'])->default('button');
            $table->timestamps();

            // 1 checkin per user per hari — idempotent
            $table->unique(['user_id', 'date'], 'checkins_user_date_unique');
            $table->index(['date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};