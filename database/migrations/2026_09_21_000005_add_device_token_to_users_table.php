<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Token acak supaya Service Worker bisa kirim checkin tanpa
            // akses localStorage (SW tidak punya akses storage).
            $table->string('device_token', 64)->nullable()->unique()->after('remember_token');
        });

        // Isi token untuk user yg sudah ada.
        foreach (\App\Models\User::whereNull('device_token')->get() as $user) {
            $user->device_token = Str::random(48);
            $user->save();
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // SQLite tidak bisa drop kolom selama index-nya masih ada.
            $table->dropUnique(['device_token']);
            $table->dropColumn('device_token');
        });
    }
};
