<?php

namespace Tests\Feature\Boards;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_see_comments_but_are_sent_to_login_to_write(): void
    {
        $comment = Comment::factory()->create(['content' => '보이는 댓글']);

        $this->get(route('posts.show', $comment->post))->assertSee('보이는 댓글');

        Volt::test('posts.comments', ['post' => $comment->post])
            ->set('content', '익명 댓글')
            ->call('add')
            ->assertRedirect(route('login'));

        $this->assertSame(1, Comment::count());
    }

    public function test_user_can_comment_and_reply(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Volt::test('posts.comments', ['post' => $post])
            ->set('content', "첫 댓글\n두 번째 줄")
            ->call('add')
            ->assertHasNoErrors()
            ->assertSee('첫 댓글');

        $parent = Comment::sole();
        $this->assertSame($user->id, $parent->user_id);

        $component->call('startReply', $parent->id)
            ->set('replyContent', '답글입니다')
            ->call('reply')
            ->assertHasNoErrors();

        $reply = Comment::latest('id')->first();
        $this->assertSame($parent->id, $reply->parent_id);

        // Replying to a reply stays one level deep.
        $component->call('startReply', $reply->id)
            ->set('replyContent', '답글의 답글')
            ->call('reply');

        $this->assertSame($parent->id, Comment::latest('id')->first()->parent_id);
    }

    public function test_comment_content_is_escaped(): void
    {
        $comment = Comment::factory()->create(['content' => '<script>alert(1)</script>']);

        $this->get(route('posts.show', $comment->post))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_only_author_can_edit_and_author_or_admin_can_delete(): void
    {
        $comment = Comment::factory()->create();
        $post = $comment->post;

        $this->actingAs(User::factory()->create());
        Volt::test('posts.comments', ['post' => $post])->call('startEdit', $comment->id)->assertForbidden();
        Volt::test('posts.comments', ['post' => $post])->call('delete', $comment->id)->assertForbidden();

        $this->actingAs($comment->user);
        Volt::test('posts.comments', ['post' => $post])
            ->call('startEdit', $comment->id)
            ->set('editContent', '고친 댓글')
            ->call('update')
            ->assertHasNoErrors();
        $this->assertSame('고친 댓글', $comment->refresh()->content);

        $this->actingAs(User::factory()->admin()->create());
        Volt::test('posts.comments', ['post' => $post])->call('delete', $comment->id);
        $this->assertSoftDeleted($comment);
    }

    public function test_comments_of_another_post_cannot_be_touched(): void
    {
        $comment = Comment::factory()->create();
        $otherPost = Post::factory()->create();

        $this->actingAs($comment->user);

        Volt::test('posts.comments', ['post' => $otherPost])->call('delete', $comment->id)->assertNotFound();
        $this->assertNotSoftDeleted($comment);
    }

    public function test_deleted_parent_with_replies_shows_placeholder(): void
    {
        $parent = Comment::factory()->create(['content' => '지울 댓글']);
        Comment::factory()->for($parent->post)->create(['parent_id' => $parent->id, 'content' => '남은 답글']);
        $parent->delete();

        Volt::test('posts.comments', ['post' => $parent->post])
            ->assertDontSee('지울 댓글')
            ->assertSee('삭제된 댓글입니다.')
            ->assertSee('남은 답글');
    }

    public function test_comment_votes_toggle(): void
    {
        $comment = Comment::factory()->create();
        $voter = User::factory()->create();
        $this->actingAs($voter);

        $component = Volt::test('posts.comments', ['post' => $comment->post]);

        $component->call('vote', $comment->id, 1);
        $this->assertSame(1, $comment->voteOf($voter));

        $component->call('vote', $comment->id, -1);
        $this->assertSame(-1, $comment->voteOf($voter));

        $component->call('vote', $comment->id, -1);
        $this->assertSame(0, $comment->voteOf($voter));
    }

    public function test_cannot_vote_own_comment(): void
    {
        $comment = Comment::factory()->create();
        $this->actingAs($comment->user);

        Volt::test('posts.comments', ['post' => $comment->post])->call('vote', $comment->id, 1)->assertForbidden();
    }
}
