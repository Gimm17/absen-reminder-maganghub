<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('push_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Endpoint URL bisa panjang (FCM ~150 char, tapi WNS/MS sampai 500+, kasih 1024 aman).
            $table->string('endpoint', 1024);
            $table->string('p256dh', 256);
            $table->string('auth', 64);
            $table->string('content_encoding', 32)->default('aesgcm');
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            // Endpoint harus unique per user (browser/device yg sama subscribe 2x = update)
            $table->unique(['user_id', 'endpoint'], 'push_endpoints_user_endpoint_unique');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_endpoints');
    }
};