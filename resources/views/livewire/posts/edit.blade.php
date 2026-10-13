<?php

use App\Livewire\Forms\PostForm;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public PostForm $form;

    public function mount(Post $post): void
    {
        $this->authorize('update', $post);
        $post->board->ensureVisibleTo(auth()->user());

        $this->form->setPost($post);
    }

    public function rendering(View $view): void
    {
        $view->title(__('Edit post'));
    }

    public function removeAttachment(int $id): void
    {
        $this->form->removedAttachmentIds = array_values(array_unique([...$this->form->removedAttachmentIds, $id]));
    }

    public function restoreAttachment(int $id): void
    {
        $this->form->removedAttachmentIds = array_values(array_diff($this->form->removedAttachmentIds, [$id]));
    }

    public function save(): void
    {
        $this->authorize('update', $this->form->post);

        $post = $this->form->update();

        $this->redirectRoute('posts.show', $post, navigate: true);
    }

    public function with(): array
    {
        return [
            'existingAttachments' => $this->form->post->attachments,
            'cancelUrl' => route('posts.show', $this->form->post),
        ];
    }
}; ?>

<div class="flex max-w-3xl flex-col gap-6">
    <div>
        <flux:subheading>{{ $form->board->name }}</flux:subheading>
        <flux:heading size="xl" level="1">{{ __('Edit post') }}</flux:heading>
    </div>

    @include('partials.post-form')
</div>
