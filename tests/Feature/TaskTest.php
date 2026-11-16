<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_their_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'Walk the dog',
            'description' => 'Around the block',
        ]);

        $this->actingAs($user);

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Walk the dog');

        $this->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee('Walk the dog')
            ->assertSee('Around the block');

        $this->get(route('tasks.create'))->assertOk();

        $this->get(route('tasks.edit', $task))
            ->assertOk()
            ->assertSee('Walk the dog');
    }

    public function test_users_cannot_see_other_users_tasks(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($other)->create([
            'title' => 'Secret task',
            'description' => 'Hidden details',
        ]);

        $this->actingAs($user);

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertDontSee('Secret task');

        $this->get(route('tasks.show', $task))->assertNotFound();
        $this->get(route('tasks.edit', $task))->assertNotFound();

        $this->put(route('tasks.update', $task), [
            'title' => 'Hijacked',
        ])->assertNotFound();

        $this->delete(route('tasks.destroy', $task))->assertNotFound();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Secret task',
            'user_id' => $other->id,
        ]);
    }

    public function test_users_can_create_a_task(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Buy milk',
            'description' => 'Whole milk',
            'due_date' => '2026-12-01',
            'is_completed' => '0',
            'user_id' => $other->id,
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('status', 'Task created.');

        $task = Task::query()->where('title', 'Buy milk')->first();

        $this->assertNotNull($task);
        $this->assertSame($user->id, $task->user_id);
        $this->assertSame('Whole milk', $task->description);
        $this->assertSame('2026-12-01', $task->due_date?->toDateString());
        $this->assertFalse($task->is_completed);
    }

    public function test_users_can_update_their_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'Buy milk',
            'description' => 'Whole milk',
            'due_date' => '2026-12-01',
            'is_completed' => false,
        ]);

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Buy oat milk',
            'description' => 'Two cartons',
            'due_date' => '2026-12-15',
            'is_completed' => '1',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('status', 'Task updated.');

        $task->refresh();

        $this->assertSame('Buy oat milk', $task->title);
        $this->assertSame('Two cartons', $task->description);
        $this->assertSame('2026-12-15', $task->due_date?->toDateString());
        $this->assertTrue($task->is_completed);
        $this->assertSame($user->id, $task->user_id);
    }

    public function test_users_can_delete_their_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'Throw away the list',
        ]);

        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'))
            ->assertSessionHas('status', 'Task deleted.');

        $this->assertModelMissing($task);
    }

    public function test_users_can_mark_tasks_complete_and_incomplete(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'Write the report',
            'description' => 'Remember the details',
            'due_date' => '2026-12-01',
            'is_completed' => false,
        ]);

        $this->actingAs($user);

        $this->put(route('tasks.update', $task), [
            'title' => $task->title,
            'is_completed' => '1',
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('tasks.index'));

        $task->refresh();

        $this->assertTrue($task->is_completed);
        $this->assertSame('Remember the details', $task->description);
        $this->assertSame('2026-12-01', $task->due_date?->toDateString());

        $this->put(route('tasks.update', $task), [
            'title' => $task->title,
            'is_completed' => '0',
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('tasks.index'));

        $this->assertFalse($task->refresh()->is_completed);
        $this->assertSame('Remember the details', $task->description);
    }

    public function test_all_active_and_completed_filters_return_the_correct_tasks(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Task::factory()->for($user)->create([
            'title' => 'Write tests',
            'is_completed' => false,
        ]);
        Task::factory()->for($user)->create([
            'title' => 'Ship feature',
            'is_completed' => true,
        ]);
        Task::factory()->for($other)->create([
            'title' => 'Someone elses task',
            'is_completed' => false,
        ]);

        $this->actingAs($user);

        $this->get(route('tasks.index', ['status' => 'all']))
            ->assertOk()
            ->assertSee('Write tests')
            ->assertSee('Ship feature')
            ->assertDontSee('Someone elses task');

        $this->get(route('tasks.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('Write tests')
            ->assertDontSee('Ship feature')
            ->assertDontSee('Someone elses task');

        $this->get(route('tasks.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Ship feature')
            ->assertDontSee('Write tests')
            ->assertDontSee('Someone elses task');

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Write tests')
            ->assertSee('Ship feature')
            ->assertDontSee('Someone elses task');

        $this->get(route('tasks.index', ['status' => 'archived']))
            ->assertOk()
            ->assertSee('Write tests')
            ->assertSee('Ship feature')
            ->assertDontSee('Someone elses task');
    }

    public function test_invalid_task_input_is_rejected(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'Keep me',
            'description' => 'Original details',
            'due_date' => '2026-12-01',
        ]);

        $this->actingAs($user);

        $this->post(route('tasks.store'), [])
            ->assertSessionHasErrors('title');

        $this->post(route('tasks.store'), [
            'title' => str_repeat('a', 256),
        ])->assertSessionHasErrors('title');

        $this->post(route('tasks.store'), [
            'title' => 'Dated task',
            'due_date' => 'not-a-date',
        ])->assertSessionHasErrors('due_date');

        $this->post(route('tasks.store'), [
            'title' => 'Boolean task',
            'is_completed' => 'not-boolean',
        ])->assertSessionHasErrors('is_completed');

        $this->put(route('tasks.update', $task), [
            'title' => '',
            'due_date' => 'not-a-date',
        ])->assertSessionHasErrors(['title', 'due_date']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Keep me',
            'description' => 'Original details',
        ]);
        $this->assertDatabaseCount('tasks', 1);
    }

    public function test_guests_cannot_access_task_routes(): void
    {
        $task = Task::factory()->for(User::factory())->create([
            'title' => 'Private task',
        ]);

        $this->get(route('tasks.index'))->assertRedirect(route('login'));
        $this->get(route('tasks.create'))->assertRedirect(route('login'));
        $this->post(route('tasks.store'), [
            'title' => 'Should not be saved',
        ])->assertRedirect(route('login'));
        $this->get(route('tasks.show', $task))->assertRedirect(route('login'));
        $this->get(route('tasks.edit', $task))->assertRedirect(route('login'));
        $this->put(route('tasks.update', $task), [
            'title' => 'Changed by a guest',
        ])->assertRedirect(route('login'));
        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Private task',
        ]);
        $this->assertDatabaseMissing('tasks', [
            'title' => 'Should not be saved',
        ]);
    }
}
