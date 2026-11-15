<x-layouts::app :title="__('Tasks')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" level="1">{{ __('Tasks') }}</flux:heading>
                <flux:subheading>{{ __('Keep track of what you need to do.') }}</flux:subheading>
            </div>

            <flux:button variant="primary" :href="route('tasks.create')" wire:navigate>
                {{ __('New task') }}
            </flux:button>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach (['all' => __('All'), 'active' => __('Active'), 'completed' => __('Completed')] as $filter => $label)
                <flux:button
                    size="sm"
                    :variant="$status === $filter ? 'primary' : 'outline'"
                    :href="route('tasks.index', ['status' => $filter])"
                    wire:navigate
                >
                    {{ $label }}
                </flux:button>
            @endforeach
        </div>

        <x-auth-session-status :status="session('status')" />

        @if ($tasks->isEmpty())
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                @if ($status === 'active')
                    <flux:heading>{{ __('No active tasks') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Tasks you still need to do will show up here.') }}</flux:text>
                @elseif ($status === 'completed')
                    <flux:heading>{{ __('No completed tasks') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Tasks you mark complete will show up here.') }}</flux:text>
                @else
                    <flux:heading>{{ __('No tasks yet') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Create your first task to get started.') }}</flux:text>
                @endif
            </div>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($tasks as $task)
                    <article class="flex flex-col gap-4 rounded-xl border border-neutral-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-neutral-700">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <flux:link :href="route('tasks.show', $task)" class="truncate font-medium" wire:navigate>
                                    {{ $task->title }}
                                </flux:link>

                                @if ($task->is_completed)
                                    <flux:badge color="green">{{ __('Completed') }}</flux:badge>
                                @else
                                    <flux:badge color="zinc">{{ __('Open') }}</flux:badge>
                                @endif
                            </div>

                            @if ($task->due_date)
                                <flux:text class="mt-1">{{ __('Due :date', ['date' => $task->due_date->toFormattedDateString()]) }}</flux:text>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('tasks.update', $task) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="title" value="{{ $task->title }}">
                                <input type="hidden" name="is_completed" value="{{ $task->is_completed ? '0' : '1' }}">
                                <flux:button type="submit" size="sm">
                                    {{ $task->is_completed ? __('Mark incomplete') : __('Mark complete') }}
                                </flux:button>
                            </form>

                            <flux:button size="sm" :href="route('tasks.edit', $task)" wire:navigate>
                                {{ __('Edit') }}
                            </flux:button>

                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('{{ __('Delete this task?') }}')">
                                @csrf
                                @method('DELETE')
                                <flux:button type="submit" variant="danger" size="sm">
                                    {{ __('Delete') }}
                                </flux:button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts::app>
