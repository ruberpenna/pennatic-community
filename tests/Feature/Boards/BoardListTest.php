<?php

namespace Tests\Feature\Boards;

use App\Models\Board;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class BoardListTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_see_home_and_board_list(): void
    {
        $post = Post::factory()->create(['title' => '첫 번째 글']);

        $this->get('/')->assertOk()->assertSee($post->board->name)->assertSee('첫 번째 글');

        $this->get(route('boards.show', $post->board))
            ->assertOk()
            ->assertSee('첫 번째 글')
            ->assertDontSee(route('posts.create', $post->board));
    }

    public function test_dashboard_redirects_to_home(): void
    {
        $this->get('/dashboard')->assertRedirect('/');
    }

    public function test_deleted_posts_are_hidden(): void
    {
        $post = Post::factory()->create(['title' => '지워진 글']);
        $post->delete();

        $this->get(route('boards.show', $post->board))->assertDontSee('지워진 글');
        $this->get(route('posts.show', $post))->assertNotFound();
    }

    public function test_posts_can_be_searched_by_title_and_author(): void
    {
        $board = Board::factory()->create();
        $author = User::factory()->create(['name' => '글쓴이']);
        Post::factory()->for($board)->for($author)->create(['title' => '라라벨 질문']);
        Post::factory()->for($board)->create(['title' => '점심 메뉴']);

        Volt::test('boards.show', ['board' => $board])
            ->set('search', '라라벨')
            ->call('applySearch')
            ->assertSee('라라벨 질문')
            ->assertDontSee('점심 메뉴');

        Volt::test('boards.show', ['board' => $board])
            ->set('field', 'author')
            ->set('search', '글쓴')
            ->call('applySearch')
            ->assertSee('라라벨 질문')
            ->assertDontSee('점심 메뉴');
    }

    public function test_like_wildcards_in_search_are_literal(): void
    {
        $board = Board::factory()->create();
        Post::factory()->for($board)->create(['title' => '100% 확실']);
        Post::factory()->for($board)->create(['title' => '아무 글']);

        Volt::test('boards.show', ['board' => $board])
            ->set('search', '%')
            ->call('applySearch')
            ->assertSee('100% 확실')
            ->assertDontSee('아무 글');
    }

    public function test_posts_can_be_filtered_by_subject(): void
    {
        $board = Board::factory()->create(['subjects' => ['질문', '정보']]);
        Post::factory()->for($board)->create(['subject' => '질문', 'title' => '질문 글']);
        Post::factory()->for($board)->create(['subject' => '정보', 'title' => '정보 글']);

        Volt::test('boards.show', ['board' => $board])
            ->call('filterSubject', '정보')
            ->assertSee('정보 글')
            ->assertDontSee('질문 글');
    }

    public function test_pinned_posts_are_listed_first(): void
    {
        $board = Board::factory()->create();
        $pinned = Post::factory()->for($board)->create(['title' => '고정된 공지']);
        $pinned->forceFill(['is_pinned' => true])->save();
        Post::factory()->for($board)->create(['title' => '최신 글']);

        Volt::test('boards.show', ['board' => $board])
            ->assertSeeInOrder(['고정된 공지', '최신 글']);
    }
}
