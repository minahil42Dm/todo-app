<x-layouts::app :title="__('Edit task')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl" level="1">{{ __('Edit task') }}</flux:heading>
            <flux:subheading>{{ __('Update the details of this task.') }}</flux:subheading>
        </div>

        <x-auth-session-status :status="session('status')" />

        @if ($errors->any())
            <flux:callout variant="danger" icon="x-circle" :heading="__('Please correct the highlighted fields.')" />
        @endif

        <form method="POST" action="{{ route('tasks.update', $task) }}" class="max-w-lg space-y-6">
            @csrf
            @method('PUT')

            <flux:input
                name="title"
                :label="__('Title')"
                :value="old('title', $task->title)"
                type="text"
                required
                autofocus
                maxlength="255"
            />

            <flux:textarea name="description" :label="__('Description')" rows="4">{{ old('description', $task->description) }}</flux:textarea>

            <flux:input
                name="due_date"
                :label="__('Due date')"
                :value="old('due_date', $task->due_date?->toDateString())"
                type="date"
            />

            <input type="hidden" name="is_completed" value="0">
            <flux:checkbox
                name="is_completed"
                value="1"
                :label="__('Completed')"
                :checked="filter_var(old('is_completed', $task->is_completed), FILTER_VALIDATE_BOOLEAN)"
            />

            <div class="flex flex-wrap items-center gap-3">
                <flux:button variant="primary" type="submit">{{ __('Save changes') }}</flux:button>
                <flux:button :href="route('tasks.show', $task)" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>

        <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('{{ __('Delete this task?') }}')">
            @csrf
            @method('DELETE')
            <flux:button type="submit" variant="danger">{{ __('Delete task') }}</flux:button>
        </form>
    </div>
</x-layouts::app>
