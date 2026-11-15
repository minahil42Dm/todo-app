<x-layouts::app :title="$task->title">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <flux:heading size="xl" level="1">{{ $task->title }}</flux:heading>
                <flux:subheading>
                    @if ($task->due_date)
                        {{ __('Due :date', ['date' => $task->due_date->toFormattedDateString()]) }}
                    @else
                        {{ __('No due date') }}
                    @endif
                </flux:subheading>
            </div>

            @if ($task->is_completed)
                <flux:badge color="green" size="lg">{{ __('Completed') }}</flux:badge>
            @else
                <flux:badge color="zinc" size="lg">{{ __('Open') }}</flux:badge>
            @endif
        </div>

        <x-auth-session-status :status="session('status')" />

        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            @if ($task->description)
                <flux:text class="whitespace-pre-wrap">{{ $task->description }}</flux:text>
            @else
                <flux:text>{{ __('No description.') }}</flux:text>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="POST" action="{{ route('tasks.update', $task) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="title" value="{{ $task->title }}">
                <input type="hidden" name="is_completed" value="{{ $task->is_completed ? '0' : '1' }}">
                <flux:button type="submit" variant="primary">
                    {{ $task->is_completed ? __('Mark incomplete') : __('Mark complete') }}
                </flux:button>
            </form>

            <flux:button :href="route('tasks.edit', $task)" wire:navigate>{{ __('Edit') }}</flux:button>

            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('{{ __('Delete this task?') }}')">
                @csrf
                @method('DELETE')
                <flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
            </form>

            <flux:button :href="route('tasks.index')" wire:navigate>{{ __('Back to tasks') }}</flux:button>
        </div>
    </div>
</x-layouts::app>
