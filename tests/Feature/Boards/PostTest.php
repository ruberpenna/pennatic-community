<?php

namespace Tests\Feature\Boards;

use App\Models\Board;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_open_the_write_page(): void
    {
        $board = Board::factory()->create();

        $this->get(route('posts.create', $board))->assertRedirect(route('login'));
    }

    public function test_user_can_write_a_post_with_attachment(): void
    {
        Storage::fake('local');
        $board = Board::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('posts.create', ['board' => $board])
            ->set('form.title', '안녕하세요')
            ->set('form.content', '<div>첫 글입니다</div>')
            ->set('form.uploads', [UploadedFile::fake()->create('자료.pdf', 100, 'application/pdf')])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $post = Post::sole();
        $this->assertSame($user->id, $post->user_id);
        $this->assertSame($board->id, $post->board_id);
        $this->assertSame('<div>첫 글입니다</div>', $post->content);

        $attachment = $post->attachments()->sole();
        $this->assertSame('자료.pdf', $attachment->original_name);
        Storage::disk('local')->assertExists($attachment->path);
    }

    public function test_post_html_is_sanitized(): void
    {
        $board = Board::factory()->create();
        $this->actingAs(User::factory()->create());

        Volt::test('posts.create', ['board' => $board])
            ->set('form.title', 'xss')
            ->set('form.content', '<div onclick="alert(1)">hi<script>alert(1)</script><a href="javascript:alert(1)">x</a></div>')
            ->call('save')
            ->assertHasNoErrors();

        $content = Post::sole()->content;
        $this->assertStringNotContainsString('script', $content);
        $this->assertStringNotContainsString('onclick', $content);
        $this->assertStringNotContainsString('javascript:', $content);
        $this->assertStringContainsString('hi', $content);
    }

    public function test_blank_content_is_rejected(): void
    {
        $board = Board::factory()->create();
        $this->actingAs(User::factory()->create());

        Volt::test('posts.create', ['board' => $board])
            ->set('form.title', '제목')
            ->set('form.content', '<div><br></div><script>x</script>')
            ->call('save')
            ->assertHasErrors('form.content');

        $this->assertSame(0, Post::count());
    }

    public function test_dangerous_attachments_are_rejected(): void
    {
        Storage::fake('local');
        $board = Board::factory()->create();
        $this->actingAs(User::factory()->create());

        Volt::test('posts.create', ['board' => $board])
            ->set('form.title', '제목')
            ->set('form.content', '<div>내용</div>')
            ->set('form.uploads', [UploadedFile::fake()->create('shell.php', 1, 'application/x-php')])
            ->call('save')
            ->assertHasErrors('form.uploads.0');
    }

    public function test_subject_must_belong_to_the_board(): void
    {
        $board = Board::factory()->create(['subjects' => ['질문']]);
        $this->actingAs(User::factory()->create());

        Volt::test('posts.create', ['board' => $board])
            ->set('form.subject', '없는말머리')
            ->set('form.title', '제목')
            ->set('form.content', '<div>내용</div>')
            ->call('save')
            ->assertHasErrors('form.subject');
    }

    public function test_view_count_increases_once_per_session(): void
    {
        $post = Post::factory()->create();

        $this->get(route('posts.show', $post))->assertOk()->assertSee($post->title);
        $this->get(route('posts.show', $post))->assertOk();

        $this->assertSame(1, $post->refresh()->view_count);
    }

    public function test_author_can_edit_and_remove_attachments(): void
    {
        Storage::fake('local');
        $post = Post::factory()->create();
        $attachment = $post->addAttachment(UploadedFile::fake()->create('old.txt', 1, 'text/plain'));

        $this->actingAs($post->user);

        Volt::test('posts.edit', ['post' => $post])
            ->set('form.title', '수정된 제목')
            ->call('removeAttachment', $attachment->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('수정된 제목', $post->refresh()->title);
        $this->assertSame(0, $post->attachments()->count());
        Storage::disk('local')->assertMissing($attachment->path);
    }

    public function test_other_users_cannot_edit_or_delete(): void
    {
        $post = Post::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->get(route('posts.edit', $post))->assertForbidden();

        Volt::test('posts.show', ['post' => $post])
            ->call('delete')
            ->assertForbidden();

        $this->assertNotSoftDeleted($post);
    }

    public function test_admin_can_delete_and_pin(): void
    {
        $post = Post::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        Volt::test('posts.show', ['post' => $post])->call('togglePin');
        $this->assertTrue($post->refresh()->is_pinned);

        Volt::test('posts.show', ['post' => $post])->call('delete')->assertRedirect(route('boards.show', $post->board));
        $this->assertSoftDeleted($post);
    }

    public function test_members_cannot_pin(): void
    {
        $post = Post::factory()->create();
        $this->actingAs($post->user);

        Volt::test('posts.show', ['post' => $post])->call('togglePin')->assertForbidden();
    }

    public function test_attachment_download_requires_login(): void
    {
        Storage::fake('local');
        $post = Post::factory()->create();
        $attachment = $post->addAttachment(UploadedFile::fake()->create('자료.txt', 1, 'text/plain'));

        $this->get(route('attachments.download', $attachment))->assertRedirect(route('login'));

        $response = $this->actingAs(User::factory()->create())
            ->get(route('attachments.download', $attachment))
            ->assertOk();

        $this->assertStringContainsString("filename*=utf-8''".rawurlencode('자료.txt'), $response->headers->get('Content-Disposition'));

        $this->assertSame(1, $attachment->refresh()->download_count);
    }

    public function test_attachments_of_deleted_posts_cannot_be_downloaded(): void
    {
        Storage::fake('local');
        $post = Post::factory()->create();
        $attachment = $post->addAttachment(UploadedFile::fake()->create('a.txt', 1, 'text/plain'));
        $post->delete();

        $this->actingAs(User::factory()->create())
            ->get(route('attachments.download', $attachment))
            ->assertNotFound();
    }
}
