<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ReminderCommandTest extends TestCase
{
    // Pakai DatabaseMigrations (bukan RefreshDatabase) supaya artisan sub-process
    // bisa baca user yg baru di-seed oleh test.
    use DatabaseMigrations;

    public function test_dry_run_does_not_fail(): void
    {
        // 3 user aktif dengan notify_slot_1 ON, 2 user non-aktif.
        User::factory()->count(3)->create([
            'is_active' => true,
            'notify_slot_1' => true,
            'notify_slot_2' => false,
            'notify_slot_3' => false,
        ]);
        User::factory()->count(2)->create(['is_active' => false]);

        $activeUsers = User::where('is_active', true)
            ->where('notify_slot_1', true)
            ->count();
        $this->assertEquals(3, $activeUsers, 'Factory harus create 3 user aktif dgn notify_slot_1.');

        // Dry-run jalan tanpa error, exit code 0.
        $this->artisan('reminders:send slot-1 --dry-run')
             ->assertSuccessful();
    }

    public function test_command_fails_for_invalid_slot(): void
    {
        $this->artisan('reminders:send bogus-slot')
             ->assertFailed();
    }
}