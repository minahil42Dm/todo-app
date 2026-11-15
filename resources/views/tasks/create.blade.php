<x-layouts::app :title="__('New task')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl" level="1">{{ __('New task') }}</flux:heading>
            <flux:subheading>{{ __('Add a task to your list.') }}</flux:subheading>
        </div>

        <x-auth-session-status :status="session('status')" />

        @if ($errors->any())
            <flux:callout variant="danger" icon="x-circle" :heading="__('Please correct the highlighted fields.')" />
        @endif

        <form method="POST" action="{{ route('tasks.store') }}" class="max-w-lg space-y-6">
            @csrf

            <flux:input
                name="title"
                :label="__('Title')"
                :value="old('title')"
                type="text"
                required
                autofocus
                maxlength="255"
            />

            <flux:textarea name="description" :label="__('Description')" rows="4">{{ old('description') }}</flux:textarea>

            <flux:input
                name="due_date"
                :label="__('Due date')"
                :value="old('due_date')"
                type="date"
            />

            <input type="hidden" name="is_completed" value="0">
            <flux:checkbox
                name="is_completed"
                value="1"
                :label="__('Completed')"
                :checked="filter_var(old('is_completed', false), FILTER_VALIDATE_BOOLEAN)"
            />

            <div class="flex items-center gap-3">
                <flux:button variant="primary" type="submit">{{ __('Create task') }}</flux:button>
                <flux:button :href="route('tasks.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
