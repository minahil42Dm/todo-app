<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create();

        Task::factory()
            ->count(10)
            ->for($user)
            ->create();
    }
}
