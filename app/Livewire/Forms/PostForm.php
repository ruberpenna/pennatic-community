<?php

namespace App\Livewire\Forms;

use App\Models\Board;
use App\Models\Post;
use App\Models\User;
use App\Support\PostContent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;

class PostForm extends Form
{
    public const MAX_ATTACHMENTS = 5;

    public const MAX_ATTACHMENT_KB = 10240;

    public const ATTACHMENT_TYPES = 'jpg,jpeg,png,gif,webp,pdf,txt,zip,7z,hwp,hwpx,doc,docx,xls,xlsx,ppt,pptx';

    public ?Board $board = null;

    public ?Post $post = null;

    public string $subject = '';

    public string $title = '';

    public string $content = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $uploads = [];

    /** @var list<int> */
    public array $removedAttachmentIds = [];

    public function forBoard(Board $board): void
    {
        $this->board = $board;
    }

    public function setPost(Post $post): void
    {
        $this->post = $post;
        $this->board = $post->board;
        $this->subject = $post->subject ?? '';
        $this->title = $post->title;
        $this->content = $post->content;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $kept = $this->post
            ? $this->post->attachments()->whereNotIn('id', $this->removedAttachmentIds)->count()
            : 0;

        return [
            'subject' => ['nullable', 'string', Rule::in($this->board?->subjects ?? [])],
            'title' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:200000'],
            'uploads' => ['array', 'max:'.(self::MAX_ATTACHMENTS - $kept)],
            'uploads.*' => ['file', 'max:'.self::MAX_ATTACHMENT_KB, 'mimes:'.self::ATTACHMENT_TYPES],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'subject' => __('Subject'),
            'title' => __('Title'),
            'content' => __('Content'),
            'uploads' => __('Attachments'),
            'uploads.*' => __('Attachment'),
        ];
    }

    /**
     * @return array{subject: ?string, title: string, content: string}
     */
    private function validatedAttributes(): array
    {
        $this->validate();

        $content = PostContent::clean($this->content);

        if (PostContent::isBlank($content)) {
            throw ValidationException::withMessages([
                'form.content' => __('validation.required', ['attribute' => __('Content')]),
            ]);
        }

        return [
            'subject' => $this->subject ?: null,
            'title' => trim($this->title),
            'content' => $content,
        ];
    }

    public function store(User $user): Post
    {
        $attributes = $this->validatedAttributes();

        return DB::transaction(function () use ($attributes, $user) {
            $post = new Post($attributes);
            $post->board()->associate($this->board);
            $post->user()->associate($user);
            $post->save();

            $this->storeUploads($post);

            return $post;
        });
    }

    public function update(): Post
    {
        $attributes = $this->validatedAttributes();

        DB::transaction(function () use ($attributes) {
            $this->post->update($attributes);

            // Deleting each model also removes its file.
            $this->post->attachments()->whereIn('id', $this->removedAttachmentIds)->get()->each->delete();

            $this->storeUploads($this->post);
        });

        return $this->post;
    }

    private function storeUploads(Post $post): void
    {
        foreach ($this->uploads as $file) {
            $post->addAttachment($file);
        }

        $this->uploads = [];
    }
}
