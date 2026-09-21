<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah nilai 'notification' ke kolom checkins.source.
 *
 * Dibutuhkan karena tombol "Sudah Absen" di notifikasi mengirim
 * source='notification'. Sebelumnya enum hanya button|manual, sehingga
 * insert ditolak (SQLite: CHECK constraint, MySQL: data truncated).
 *
 * SQLite tidak mendukung ALTER ENUM, jadi kolom ditulis ulang sebagai
 * string dengan validasi di level aplikasi (sudah ada di controller).
 */
return new class extends Migration {
    public function up(): void
    {
        // SQLite: ubah ke string. MySQL: ubah daftar enum-nya.
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite menyimpan enum sebagai varchar + CHECK constraint.
            // Cara paling aman & portabel: recreate kolom sebagai string.
            Schema::table('checkins', function (Blueprint $table) {
                $table->string('source', 20)->default('button')->change();
            });
            return;
        }

        Schema::table('checkins', function (Blueprint $table) {
            $table->enum('source', ['button', 'manual', 'notification'])
                ->default('button')
                ->change();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('checkins', function (Blueprint $table) {
                $table->string('source', 20)->default('button')->change();
            });
            return;
        }

        Schema::table('checkins', function (Blueprint $table) {
            $table->enum('source', ['button', 'manual'])->default('button')->change();
        });
    }
};
