<?php

use App\Models\Memo;
use App\Support\Like;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public ?int $editingId = null;

    public string $title = '';

    public string $content = '';

    public function rendering(View $view): void
    {
        $view->title(__('Memos'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset('editingId', 'title', 'content');
        $this->resetValidation();
        $this->modal('memo-form')->show();
    }

    public function edit(int $id): void
    {
        $memo = $this->findMemo($id);

        $this->editingId = $memo->id;
        $this->title = $memo->title;
        $this->content = $memo->content;
        $this->resetValidation();
        $this->modal('memo-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:10000'],
        ], attributes: [
            'title' => __('Title'),
            'content' => __('Content'),
        ]);

        if ($this->editingId) {
            $this->findMemo($this->editingId)->update($validated);
        } else {
            auth()->user()->memos()->create($validated);
        }

        $this->reset('editingId', 'title', 'content');
        $this->modal('memo-form')->close();
    }

    public function delete(int $id): void
    {
        $this->findMemo($id)->delete();
    }

    public function with(): array
    {
        $term = trim($this->search);

        return [
            'memos' => auth()->user()->memos()
                ->when($term !== '', fn ($query) => $query->where(
                    fn ($q) => Like::where(Like::where($q, 'title', $term), 'content', $term, 'or'),
                ))
                ->latest('updated_at')
                ->paginate(12),
        ];
    }

    private function findMemo(int $id): Memo
    {
        // Scoped to the current user, so other users' memos are simply not found.
        return auth()->user()->memos()->findOrFail($id);
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Memos') }}</flux:heading>
            <flux:subheading>{{ __('Only you can see your memos.') }}</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">{{ __('New memo') }}</flux:button>
    </div>

    <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass" :placeholder="__('Search memos')" class="max-w-sm" :aria-label="__('Search memos')" />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($memos as $memo)
            <div class="flex flex-col gap-2 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700" wire:key="memo-{{ $memo->id }}">
                <flux:heading class="truncate">{{ $memo->title }}</flux:heading>
                <p class="line-clamp-5 whitespace-pre-line break-words text-sm text-zinc-600 dark:text-zinc-300">{{ $memo->content }}</p>
                <div class="mt-auto flex items-center justify-between pt-2">
                    <time class="text-xs text-zinc-500" datetime="{{ $memo->updated_at->toIso8601String() }}">{{ $memo->updated_at->format('Y.m.d H:i') }}</time>
                    <div class="flex gap-1">
                        <flux:button size="xs" variant="ghost" icon="pencil" wire:click="edit({{ $memo->id }})" :aria-label="__('Edit')" />
                        <flux:button size="xs" variant="ghost" icon="trash" wire:click="delete({{ $memo->id }})" wire:confirm="{{ __('Delete this memo?') }}" :aria-label="__('Delete')" />
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full py-10 text-center text-zinc-500">
                {{ $search !== '' ? __('No memos match your search.') : __('No memos yet.') }}
            </p>
        @endforelse
    </div>

    {{ $memos->links() }}

    <flux:modal name="memo-form" class="w-full max-w-lg">
        <form wire:submit="save" class="flex flex-col gap-6">
            <flux:heading size="lg">{{ $editingId ? __('Edit memo') : __('New memo') }}</flux:heading>
            <flux:input wire:model="title" :label="__('Title')" maxlength="100" />
            <flux:textarea wire:model="content" :label="__('Content')" rows="8" maxlength="10000" />
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
