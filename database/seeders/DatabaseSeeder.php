<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin default. Email ini yang dipakai untuk login ke /admin.
        User::firstOrCreate(
            ['email' => 'admin@localhost'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'is_active' => true,
                'notify_slot_1' => true,
                'notify_slot_2' => true,
                'notify_slot_3' => true,
            ]
        );
    }
}