<?php

namespace App\Http\Controllers;

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
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedTask($request);

        Task::query()->create([
            ...$validated,
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
    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->ensureOwner($request, $task);

        $task->update($this->validatedTask($request));

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
     * Validate a task form submission.
     *
     * The owner is never taken from the form. A missing completed checkbox
     * means the task is not completed.
     *
     * @return array{title: string, description: string|null, due_date: string|null, is_completed: bool}
     */
    private function validatedTask(Request $request): array
    {
        $request->mergeIfMissing([
            'is_completed' => false,
        ]);

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'is_completed' => ['boolean'],
        ]);
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
