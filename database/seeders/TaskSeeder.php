<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Seed sample tasks for a dedicated demo account.
     *
     * The tasks table has no unique title, so another run would insert
     * another 10 tasks. Skip only when this demo user already owns tasks.
     * Tasks owned by other users do not block the demo data.
     */
    public function run(): void
    {
        $user = User::query()->where('email', 'demo@example.com')->first();

        if ($user === null) {
            $user = User::factory()->create([
                'name' => 'Demo User',
                'email' => 'demo@example.com',
            ]);
        }

        if (Task::query()->whereBelongsTo($user)->exists()) {
            return;
        }

        Task::factory()
            ->count(10)
            ->for($user)
            ->create();
    }
}
