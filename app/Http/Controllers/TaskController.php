<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display the authenticated user's tasks.
     */
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        if (! in_array($status, ['all', 'active', 'completed'], true)) {
            $status = 'all';
        }

        $tasks = Task::query()
            ->ownedBy($this->userId($request))
            ->when($status === 'active', fn (Builder $query) => $query->where('is_completed', false))
            ->when($status === 'completed', fn (Builder $query) => $query->where('is_completed', true))
            ->latest()
            ->get();

        return view('tasks.index', [
            'tasks' => $tasks,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(Request $request): View
    {
        $this->userId($request);

        return view('tasks.create');
    }

    /**
     * Store a new task for the authenticated user.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        Task::query()->create([
            ...$request->validated(),
            'user_id' => $this->userId($request),
        ]);

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task created.');
    }

    /**
     * Display one task owned by the authenticated user.
     */
    public function show(Request $request, Task $task): View
    {
        $this->ensureOwner($request, $task);

        return view('tasks.show', ['task' => $task]);
    }

    /**
     * Show the form for editing one task owned by the authenticated user.
     */
    public function edit(Request $request, Task $task): View
    {
        $this->ensureOwner($request, $task);

        return view('tasks.edit', ['task' => $task]);
    }

    /**
     * Update one task owned by the authenticated user.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->ensureOwner($request, $task);

        $task->update($request->validated());

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task updated.');
    }

    /**
     * Delete one task owned by the authenticated user.
     */
    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $this->ensureOwner($request, $task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task deleted.');
    }

    /**
     * Return the signed-in user's id, or stop guests before any task is read.
     */
    private function userId(Request $request): int
    {
        $userId = $request->user()?->id;

        abort_if($userId === null, 403);

        return (int) $userId;
    }

    /**
     * Stop the request when the task belongs to someone else.
     */
    private function ensureOwner(Request $request, Task $task): void
    {
        abort_unless((int) $task->user_id === $this->userId($request), 404);
    }
}
