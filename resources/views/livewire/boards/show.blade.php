<?php

use App\Models\Board;
use App\Models\Post;
use Illuminate\Support\Js;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public const PER_PAGE = 20;

    public const SEARCH_FIELDS = ['title', 'title_content', 'content', 'author'];

    public Board $board;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'title')]
    public string $field = 'title';

    #[Url(except: '')]
    public string $subject = '';

    public function mount(Board $board): void
    {
        $board->ensureVisibleTo(auth()->user());

        $this->board = $board;
    }

    public function rendering(View $view): void
    {
        $view->title($this->board->name);
    }

    public function applySearch(): void
    {
        $this->resetPage();
    }

    public function filterSubject(string $subject): void
    {
        $this->subject = in_array($subject, $this->board->subjects ?? [], true) ? $subject : '';
        $this->resetPage();
    }

    public function with(): array
    {
        $field = in_array($this->field, self::SEARCH_FIELDS, true) ? $this->field : 'title';
        $term = trim($this->search);

        $posts = $this->board->posts()
            ->where('is_pinned', false)
            ->when($term !== '', fn ($query) => $query->search($field, $term))
            ->when($this->subject !== '', fn ($query) => $query->where('subject', $this->subject))
            ->with('user')
            ->withCount('comments')->withVoteCounts()
            ->latest('id')
            ->paginate(self::PER_PAGE);

        $pinned = $posts->onFirstPage() && $term === ''
            ? $this->board->posts()->where('is_pinned', true)->with('user')->withCount('comments')->withVoteCounts()->latest('id')->get()
            : collect();

        return compact('posts', 'pinned');
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:subheading>{{ $board->folder->name }}</flux:subheading>
            <flux:heading size="xl" level="1" class="flex items-center gap-2">
                {{ $board->name }}
                @if ($board->is_private)
                    <flux:badge size="sm" icon="lock-closed">{{ __('Private') }}</flux:badge>
                @endif
            </flux:heading>
        </div>

        @can('create', Post::class)
            <flux:button variant="primary" icon="pencil-square" :href="route('posts.create', $board)" wire:navigate>{{ __('Write') }}</flux:button>
        @endcan
    </div>

    @if ($board->subjects)
        <div class="flex flex-wrap gap-2">
            <flux:button size="sm" :variant="$subject === '' ? 'primary' : 'ghost'" wire:click="filterSubject('')">{{ __('All') }}</flux:button>
            @foreach ($board->subjects as $item)
                <flux:button size="sm" :variant="$subject === $item ? 'primary' : 'ghost'" wire:click="filterSubject({{ Js::from($item) }})">{{ $item }}</flux:button>
            @endforeach
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full table-fixed text-sm">
            <thead class="border-b border-zinc-200 text-zinc-500 dark:border-zinc-700">
                <tr>
                    <th class="w-16 py-2 text-center font-normal max-sm:hidden">{{ __('No.') }}</th>
                    <th class="py-2 text-left font-normal">{{ __('Title') }}</th>
                    {{-- Narrow author/date columns on phones so the title gets the width. --}}
                    <th class="w-16 py-2 text-left font-normal sm:w-32">{{ __('Author') }}</th>
                    <th class="w-12 py-2 text-center font-normal sm:w-24">{{ __('Date') }}</th>
                    <th class="w-16 py-2 text-center font-normal max-sm:hidden">{{ __('Views') }}</th>
                    <th class="w-16 py-2 text-center font-normal max-sm:hidden">{{ __('Likes') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($pinned as $post)
                    @include('partials.post-row', ['post' => $post, 'number' => null])
                @endforeach

                @forelse ($posts as $post)
                    @include('partials.post-row', ['post' => $post, 'number' => $posts->total() - ($posts->firstItem() + $loop->index) + 1])
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-zinc-500">
                            {{ $search !== '' ? __('No posts match your search.') : __('No posts yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $posts->links() }}

    <form wire:submit="applySearch" class="flex flex-wrap justify-center gap-2">
        <flux:select wire:model="field" class="max-w-36" :aria-label="__('Search in')">
            <flux:select.option value="title">{{ __('Title') }}</flux:select.option>
            <flux:select.option value="title_content">{{ __('Title + content') }}</flux:select.option>
            <flux:select.option value="content">{{ __('Content') }}</flux:select.option>
            <flux:select.option value="author">{{ __('Author') }}</flux:select.option>
        </flux:select>
        <flux:input wire:model="search" class="max-w-64" :aria-label="__('Search')" :placeholder="__('Search')" />
        <flux:button type="submit" icon="magnifying-glass">{{ __('Search') }}</flux:button>
    </form>
</div>
