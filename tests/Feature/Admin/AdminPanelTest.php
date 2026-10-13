<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\BoardFolders\Pages\EditBoardFolder;
use App\Filament\Resources\BoardFolders\RelationManagers\BoardsRelationManager;
use App\Filament\Resources\Notices\Pages\CreateNotice;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Board;
use App\Models\BoardFolder;
use App\Models\Notice;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_only_admins_can_open_the_panel(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();

        $this->actingAs($this->admin)->get('/admin')->assertOk();
    }

    public function test_admin_must_change_password_first(): void
    {
        $this->admin->forceFill(['password_must_change' => true])->save();

        $this->actingAs($this->admin)->get('/admin')->assertRedirect(route('settings.password'));
    }

    public function test_every_admin_page_renders(): void
    {
        Post::factory()->create();
        Notice::factory()->create();
        $folder = BoardFolder::factory()->create();

        $this->actingAs($this->admin);

        foreach (['/admin/users', '/admin/board-folders', "/admin/board-folders/{$folder->id}/edit", '/admin/posts', '/admin/attachments', '/admin/notices', '/admin/notices/create', "/admin/users/{$this->admin->id}/edit"] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_can_withdraw_and_restore_a_member_but_not_themselves(): void
    {
        $member = User::factory()->create();
        $this->actingAs($this->admin);

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('delete', $this->admin)
            ->callTableAction('delete', $member);

        $this->assertSoftDeleted($member);

        Livewire::test(ListUsers::class)
            ->filterTable('trashed', true)
            ->callTableAction('restore', $member);

        $this->assertNotSoftDeleted($member);
    }

    public function test_admin_can_promote_a_member_but_not_demote_themselves(): void
    {
        $member = User::factory()->create();
        $this->actingAs($this->admin);

        Livewire::test(EditUser::class, ['record' => $member->getRouteKey()])
            ->fillForm(['role' => 'admin', 'level' => 3])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('admin', $member->refresh()->role);
        $this->assertSame(3, $member->level);

        Livewire::test(EditUser::class, ['record' => $this->admin->getRouteKey()])
            ->assertFormFieldIsDisabled('role')
            ->call('save');

        $this->assertSame('admin', $this->admin->refresh()->role);
    }

    public function test_notice_content_is_sanitized(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateNotice::class)
            ->fillForm([
                'title' => '점검 안내',
                'content' => '<p>내일 점검</p><script>alert(1)</script><p onclick="x()">끝</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $notice = Notice::sole();
        $this->assertSame($this->admin->id, $notice->user_id);
        $this->assertStringContainsString('내일 점검', $notice->content);
        $this->assertStringNotContainsString('script', $notice->content);
        $this->assertStringNotContainsString('onclick', $notice->content);
    }

    public function test_admin_can_add_boards_and_cannot_delete_boards_with_posts(): void
    {
        $folder = BoardFolder::factory()->create();
        $this->actingAs($this->admin);

        Livewire::test(BoardsRelationManager::class, ['ownerRecord' => $folder, 'pageClass' => EditBoardFolder::class])
            ->callTableAction('create', data: ['name' => '자유게시판', 'subjects' => ['잡담', '질문']])
            ->assertHasNoTableActionErrors();

        $board = Board::sole();
        $this->assertSame(['잡담', '질문'], $board->subjects);

        Post::factory()->for($board)->create();

        Livewire::test(BoardsRelationManager::class, ['ownerRecord' => $folder, 'pageClass' => EditBoardFolder::class])
            ->assertTableActionDisabled('delete', $board);
    }

    public function test_board_icon_is_chosen_from_the_allowed_set(): void
    {
        $folder = BoardFolder::factory()->create();
        $this->actingAs($this->admin);

        Livewire::test(BoardsRelationManager::class, ['ownerRecord' => $folder, 'pageClass' => EditBoardFolder::class])
            ->callTableAction('create', data: ['name' => '사진첩', 'icon' => 'photo'])
            ->assertHasNoTableActionErrors();

        $this->assertSame('photo', Board::sole()->icon);

        Livewire::test(BoardsRelationManager::class, ['ownerRecord' => $folder, 'pageClass' => EditBoardFolder::class])
            ->callTableAction('create', data: ['name' => '이상한 아이콘', 'icon' => 'trash'])
            ->assertHasTableActionErrors(['icon']);

        $this->assertSame(1, Board::count());
    }

    public function test_sidebar_uses_the_board_icon_with_a_fallback(): void
    {
        $this->assertSame('photo', Board::factory()->make(['icon' => 'photo'])->menuIcon());
        $this->assertSame(Board::DEFAULT_ICON, Board::factory()->make(['icon' => null])->menuIcon());
        $this->assertSame(Board::DEFAULT_ICON, Board::factory()->make(['icon' => 'not-allowed'])->menuIcon());
    }

    public function test_force_deleting_a_post_removes_its_files(): void
    {
        Storage::fake('local');
        $post = Post::factory()->create();
        $attachment = $post->addAttachment(UploadedFile::fake()->create('a.txt', 1, 'text/plain'));
        $post->delete();

        $this->actingAs($this->admin);

        Livewire::test(ListPosts::class)
            ->filterTable('trashed', true)
            ->callTableAction('forceDelete', $post);

        $this->assertModelMissing($post);
        $this->assertModelMissing($attachment);
        Storage::disk('local')->assertMissing($attachment->path);
    }

    public function test_admin_can_pin_from_the_post_list(): void
    {
        $post = Post::factory()->create();
        $this->actingAs($this->admin);

        Livewire::test(ListPosts::class)->callTableAction('togglePin', $post);

        $this->assertTrue($post->refresh()->is_pinned);
    }
}
