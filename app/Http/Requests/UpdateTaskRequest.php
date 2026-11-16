<?php

namespace App\Http\Requests;

use App\Models\Task;

class UpdateTaskRequest extends StoreTaskRequest
{
    /**
     * Determine if the user is authorized to update this task.
     *
     * Route binding already hides another user's task. This check rejects
     * an update when the resolved task belongs to someone else.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $task = $this->route('task');

        if ($user === null) {
            return false;
        }

        if ($task instanceof Task) {
            return (int) $task->user_id === (int) $user->id;
        }

        return true;
    }
}
