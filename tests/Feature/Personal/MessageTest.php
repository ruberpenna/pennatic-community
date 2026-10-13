<?php

namespace Tests\Feature\Personal;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_pages_require_login(): void
    {
        $this->get(route('messages.index'))->assertRedirect(route('login'));
        $this->get(route('messages.create'))->assertRedirect(route('login'));
    }

    public function test_user_can_send_a_message_by_nickname(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create(['name' => '받는이']);
        $this->actingAs($sender);

        Volt::test('messages.create')
            ->set('recipient', '받는이')
            ->set('title', '안녕하세요')
            ->set('content', '반갑습니다')
            ->call('send')
            ->assertHasNoErrors()
            ->assertRedirect(route('messages.index', ['box' => 'sent']));

        $message = Message::sole();
        $this->assertSame($sender->id, $message->sender_id);
        $this->assertSame($recipient->id, $message->recipient_id);
    }

    public function test_cannot_send_to_unknown_or_self(): void
    {
        $sender = User::factory()->create(['name' => '나']);
        $this->actingAs($sender);

        Volt::test('messages.create')
            ->set('recipient', '없는사람')->set('title', 't')->set('content', 'c')
            ->call('send')->assertHasErrors('recipient');

        Volt::test('messages.create')
            ->set('recipient', '나')->set('title', 't')->set('content', 'c')
            ->call('send')->assertHasErrors('recipient');

        $this->assertSame(0, Message::count());
    }

    public function test_sending_is_rate_limited(): void
    {
        $this->actingAs(User::factory()->create());
        User::factory()->create(['name' => '받는이']);

        for ($i = 0; $i < 10; $i++) {
            Volt::test('messages.create')
                ->set('recipient', '받는이')->set('title', "t{$i}")->set('content', 'c')
                ->call('send')->assertHasNoErrors();
        }

        Volt::test('messages.create')
            ->set('recipient', '받는이')->set('title', 'over')->set('content', 'c')
            ->call('send')->assertHasErrors('content');

        $this->assertSame(10, Message::count());
    }

    public function test_reading_marks_as_read_and_others_cannot_read(): void
    {
        $message = Message::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('messages.show', $message))
            ->assertNotFound();

        $this->actingAs($message->sender)->get(route('messages.show', $message))->assertOk();
        $this->assertNull($message->refresh()->read_at);

        $this->actingAs($message->recipient)->get(route('messages.show', $message))->assertOk()->assertSee($message->title);
        $this->assertNotNull($message->refresh()->read_at);
    }

    public function test_deleting_hides_only_one_side_until_both_delete(): void
    {
        $message = Message::factory()->create();

        $this->actingAs($message->recipient);
        Volt::test('messages.show', ['message' => $message])->call('delete');

        $message->refresh();
        $this->assertNotNull($message->recipient_deleted_at);
        $this->assertSame(0, Message::inboxOf($message->recipient)->count());
        $this->assertSame(1, Message::sentBy($message->sender)->count());

        $this->actingAs($message->sender);
        Volt::test('messages.show', ['message' => $message])->call('delete');

        $this->assertModelMissing($message);
    }

    public function test_bulk_delete_ignores_other_peoples_messages(): void
    {
        $me = User::factory()->create();
        $mine = Message::factory()->create(['recipient_id' => $me->id]);
        $notMine = Message::factory()->create();

        $this->actingAs($me);

        Volt::test('messages.index')
            ->set('selected', [$mine->id, $notMine->id])
            ->call('deleteSelected');

        $this->assertNotNull($mine->refresh()->recipient_deleted_at);
        $this->assertNull($notMine->refresh()->recipient_deleted_at);
        $this->assertNull($notMine->sender_deleted_at);
    }

    public function test_reply_prefills_recipient_and_title(): void
    {
        $message = Message::factory()->create(['title' => '질문']);

        $this->actingAs($message->recipient);

        Livewire::withQueryParams(['reply' => $message->id])
            ->test('messages.create')
            ->assertSet('title', 'Re: 질문')
            ->assertSet('recipient', $message->sender->name);

        $this->actingAs(User::factory()->create())
            ->get(route('messages.create', ['reply' => $message->id]))
            ->assertNotFound();
    }

    public function test_unread_count_is_shown_in_sidebar(): void
    {
        $me = User::factory()->create();
        Message::factory(2)->create(['recipient_id' => $me->id]);

        $this->actingAs($me)->get(route('messages.index'))->assertSeeInOrder(['쪽지', '2']);
    }

    public function test_contact_sends_a_message_to_every_admin(): void
    {
        $admins = User::factory(2)->admin()->create();
        $member = User::factory()->create();
        $this->actingAs($member);

        Volt::test('contact')
            ->set('title', '신고합니다')
            ->set('content', '스팸 글이 있어요')
            ->call('send')
            ->assertHasNoErrors();

        foreach ($admins as $admin) {
            $this->assertSame(1, Message::inboxOf($admin)->where('sender_id', $member->id)->count());
        }
    }
}
