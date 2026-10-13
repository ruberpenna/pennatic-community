{{-- Shared by posts.create and posts.edit; expects $form (PostForm) and $existingAttachments. --}}
<form wire:submit="save" class="flex flex-col gap-6">
    @if ($form->board->subjects)
        <flux:select wire:model="form.subject" :label="__('Subject')">
            <flux:select.option value="">{{ __('None') }}</flux:select.option>
            @foreach ($form->board->subjects as $subject)
                <flux:select.option :value="$subject">{{ $subject }}</flux:select.option>
            @endforeach
        </flux:select>
    @endif

    <flux:input wire:model="form.title" :label="__('Title')" maxlength="100" required />

    <x-editor model="form.content" :value="$form->content" :label="__('Content')" />

    <flux:field>
        <flux:label>{{ __('Attachments') }}</flux:label>
        <flux:description>
            {{ __('Up to :count files, :size MB each.', ['count' => \App\Livewire\Forms\PostForm::MAX_ATTACHMENTS, 'size' => \App\Livewire\Forms\PostForm::MAX_ATTACHMENT_KB / 1024]) }}
        </flux:description>

        @if ($existingAttachments->isNotEmpty())
            <ul class="flex flex-col gap-1 text-sm">
                @foreach ($existingAttachments as $attachment)
                    <li class="flex items-center gap-2" wire:key="attachment-{{ $attachment->id }}">
                        @if (in_array($attachment->id, $form->removedAttachmentIds, true))
                            <span class="text-zinc-500 line-through">{{ $attachment->original_name }}</span>
                            <flux:button size="xs" variant="ghost" wire:click="restoreAttachment({{ $attachment->id }})">{{ __('Undo') }}</flux:button>
                        @else
                            <span>{{ $attachment->original_name }}</span>
                            <flux:button size="xs" variant="ghost" icon="x-mark" wire:click="removeAttachment({{ $attachment->id }})">{{ __('Remove') }}</flux:button>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <input
            type="file"
            wire:model="form.uploads"
            multiple
            accept=".{{ str_replace(',', ',.', \App\Livewire\Forms\PostForm::ATTACHMENT_TYPES) }}"
            class="text-sm file:me-3 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 dark:file:bg-zinc-700"
        />
        <div wire:loading wire:target="form.uploads" class="text-sm text-zinc-500">{{ __('Uploading...') }}</div>

        <flux:error name="form.uploads" />
        @foreach ($errors->get('form.uploads.*') as $messages)
            <flux:error :message="$messages[0]" />
        @endforeach
    </flux:field>

    <div class="flex justify-end gap-2">
        <flux:button :href="$cancelUrl" wire:navigate>{{ __('Cancel') }}</flux:button>
        <flux:button type="submit" variant="primary" wire:loading.attr="disabled">{{ __('Save') }}</flux:button>
    </div>
</form>
