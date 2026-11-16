<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class TaskSeeder extends Seeder
{
    /**
     * Seed tasks for the existing Gmail account.
     *
     * The tasks table has no unique title, so a second run would insert
     * another 10 tasks. Skip when this user already owns tasks.
     */
    public function run(): void
    {
        $user = User::query()
            ->whereKey(1)
            ->where('email', 'minahilmirza948@gmail.com')
            ->first();

        if ($user === null) {
            throw new RuntimeException('The Gmail account minahilmirza948@gmail.com was not found as user 1.');
        }

        if (Task::query()->where('user_id', $user->id)->exists()) {
            return;
        }

        Task::factory()
            ->count(10)
            ->for($user)
            ->create();
    }
}
