<?php

namespace Tests\Feature\Boards;

use App\Models\Comment;
use App\Models\Notice;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PostVoteAndActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_vote_on_others_posts(): void
    {
        $post = Post::factory()->create();
        $voter = User::factory()->create();
        $this->actingAs($voter);

        Volt::test('posts.show', ['post' => $post])
            ->call('vote', 1)
            ->assertSet('post.id', $post->id);

        $this->assertSame(1, $post->voteOf($voter));
        $this->assertSame(1, Post::withVoteCounts()->find($post->id)->upvotes_count);
    }

    public function test_cannot_vote_own_post(): void
    {
        $post = Post::factory()->create();
        $this->actingAs($post->user);

        Volt::test('posts.show', ['post' => $post])->call('vote', 1)->assertForbidden();
    }

    public function test_guest_vote_redirects_to_login(): void
    {
        $post = Post::factory()->create();

        Volt::test('posts.show', ['post' => $post])->call('vote', 1)->assertRedirect(route('login'));
        $this->assertSame(0, $post->votes()->count());
    }

    public function test_notices_are_public(): void
    {
        $notice = Notice::factory()->create(['title' => '점검 안내', 'content' => '<p>내일 점검합니다</p>']);

        $this->get(route('notices.index'))->assertOk()->assertSee('점검 안내');
        $this->get(route('notices.show', $notice))->assertOk()->assertSee('내일 점검합니다');
    }

    public function test_my_activity_lists_only_my_posts_and_comments(): void
    {
        $me = User::factory()->create();
        Post::factory()->for($me)->create(['title' => '내가 쓴 글']);
        Post::factory()->create(['title' => '남이 쓴 글']);
        Comment::factory()->for($me)->create(['content' => '내가 쓴 댓글']);

        $this->get(route('my.activity'))->assertRedirect(route('login'));

        $this->actingAs($me);

        Volt::test('my.activity')
            ->assertSee('내가 쓴 글')
            ->assertDontSee('남이 쓴 글')
            ->set('tab', 'comments')
            ->assertSee('내가 쓴 댓글');
    }
}
