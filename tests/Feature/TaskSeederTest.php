<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Database\Seeders\TaskSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TaskSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_a_demo_user_and_sample_tasks(): void
    {
        $this->seed(TaskSeeder::class);

        $demo = User::query()->where('email', 'demo@example.com')->first();

        $this->assertNotNull($demo);
        $this->assertSame('Demo User', $demo->name);
        $this->assertTrue(Hash::isHashed($demo->password));
        $this->assertFalse(Hash::check('plain-text', $demo->password));
        $this->assertTrue(Hash::check('password', $demo->password));
        $this->assertSame(10, Task::query()->whereBelongsTo($demo)->count());
    }

    public function test_seeder_reuses_an_existing_demo_user_and_does_not_duplicate_tasks(): void
    {
        $existing = User::factory()->create([
            'name' => 'Existing Demo',
            'email' => 'demo@example.com',
        ]);

        $this->seed(TaskSeeder::class);
        $this->seed(TaskSeeder::class);

        $this->assertSame(1, User::query()->where('email', 'demo@example.com')->count());
        $this->assertSame($existing->id, User::query()->where('email', 'demo@example.com')->value('id'));
        $this->assertSame('Existing Demo', $existing->fresh()->name);
        $this->assertSame(10, Task::query()->whereBelongsTo($existing)->count());
        $this->assertSame(10, Task::query()->count());
    }

    public function test_seeder_creates_demo_tasks_when_other_users_already_have_tasks(): void
    {
        $other = User::factory()->create();
        Task::factory()->for($other)->create([
            'title' => 'Someone elses task',
        ]);

        $this->seed(TaskSeeder::class);

        $demo = User::query()->where('email', 'demo@example.com')->first();

        $this->assertNotNull($demo);
        $this->assertNotSame($other->id, $demo->id);
        $this->assertSame(10, Task::query()->whereBelongsTo($demo)->count());
        $this->assertSame(1, Task::query()->whereBelongsTo($other)->count());
        $this->assertSame(11, Task::query()->count());
    }
}
