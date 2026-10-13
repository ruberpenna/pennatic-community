<?php

namespace Tests\Feature\Boards;

use App\Filament\Resources\BoardFolders\Pages\EditBoardFolder;
use App\Filament\Resources\BoardFolders\RelationManagers\BoardsRelationManager;
use App\Models\Board;
use App\Models\BoardFolder;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PrivateBoardTest extends TestCase
{
    use RefreshDatabase;

    private Board $board;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->board = Board::factory()->create(['name' => '운영진 회의', 'is_private' => true]);
        $this->post = Post::factory()->for($this->board)->for(User::factory()->admin())->create(['title' => '비밀 안건']);
    }

    public function test_guests_and_members_cannot_see_or_reach_it(): void
    {
        foreach ([null, User::factory()->create()] as $user) {
            if ($user) {
                $this->actingAs($user);
            }

            $this->get('/')->assertOk()->assertDontSee('운영진 회의')->assertDontSee('비밀 안건');
            $this->get(route('boards.show', $this->board))->assertNotFound();
            $this->get(route('posts.show', $this->post))->assertNotFound();
        }

        $this->get(route('posts.create', $this->board))->assertNotFound();
    }

    public function test_members_cannot_comment_or_download_files_there(): void
    {
        Storage::fake('local');
        $attachment = $this->post->addAttachment(UploadedFile::fake()->create('회의록.txt', 1, 'text/plain'));

        $this->actingAs(User::factory()->create());

        $this->get(route('attachments.download', $attachment))->assertNotFound();
        Volt::test('posts.comments', ['post' => $this->post])->assertNotFound();
        $this->assertSame(0, Comment::count());
    }

    public function test_admins_see_it_with_a_lock_and_can_use_it(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/')->assertOk()->assertSee('운영진 회의');
        $this->get(route('boards.show', $this->board))->assertOk()->assertSee('비밀 안건')->assertSee('비공개');
        $this->get(route('posts.show', $this->post))->assertOk();
        $this->get(route('posts.create', $this->board))->assertOk();

        $this->assertSame(Board::PRIVATE_ICON, $this->board->menuIcon());
    }

    public function test_admin_can_make_a_board_private(): void
    {
        $folder = BoardFolder::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(BoardsRelationManager::class, ['ownerRecord' => $folder, 'pageClass' => EditBoardFolder::class])
            ->callTableAction('create', data: ['name' => '관리자 메모', 'is_private' => true])
            ->assertHasNoTableActionErrors();

        $board = Board::where('name', '관리자 메모')->sole();
        $this->assertTrue($board->is_private);
        $this->assertSame('lock-closed', $board->menuIcon());
    }
}
