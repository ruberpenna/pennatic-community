<?php

use App\Livewire\Forms\PostForm;
use App\Models\Board;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public PostForm $form;

    public function mount(Board $board): void
    {
        $this->authorize('create', Post::class);

        $this->form->forBoard($board);
    }

    public function rendering(View $view): void
    {
        $view->title(__('Write'));
    }

    public function save(): void
    {
        $this->authorize('create', Post::class);

        $post = $this->form->store(auth()->user());

        $this->redirectRoute('posts.show', $post, navigate: true);
    }

    public function with(): array
    {
        return [
            'existingAttachments' => collect(),
            'cancelUrl' => route('boards.show', $this->form->board),
        ];
    }
}; ?>

<div class="flex max-w-3xl flex-col gap-6">
    <div>
        <flux:subheading>{{ $form->board->name }}</flux:subheading>
        <flux:heading size="xl" level="1">{{ __('Write') }}</flux:heading>
    </div>

    @include('partials.post-form')
</div>
