<?php

namespace Tests\Feature\Auth;

use App\Models\Board;
use App\Models\Post;
use App\Models\User;
use App\Support\EmailAddress;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SignupProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function register(string $username, string $email): \Livewire\Features\SupportTesting\Testable
    {
        return Volt::test('auth.register')
            ->set('username', $username)
            ->set('name', 'nick'.$username)
            ->set('email', $email)
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');
    }

    public function test_signup_sends_a_verification_mail(): void
    {
        Notification::fake();

        $this->register('newbie', 'newbie@naver.com')->assertHasNoErrors();

        $user = User::where('username', 'newbie')->sole();
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_signup_still_works_when_the_mail_server_fails(): void
    {
        config(['mail.default' => 'failing']);
        config(['mail.mailers.failing' => ['transport' => 'smtp', 'host' => '127.0.0.1', 'port' => 1, 'timeout' => 1]]);

        $this->register('mailfail', 'mailfail@naver.com')
            ->assertHasNoErrors()
            ->assertRedirect(route('verification.notice', absolute: false));

        $this->assertAuthenticatedAs(User::where('username', 'mailfail')->sole());
    }

    public function test_unverified_members_can_read_but_not_write(): void
    {
        $board = Board::factory()->create();
        $post = Post::factory()->for($board)->create();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $this->get(route('boards.show', $board))->assertOk()->assertSee('이메일 인증을 완료해 주세요.');
        $this->get(route('posts.show', $post))->assertOk();

        foreach ([route('posts.create', $board), route('messages.index'), route('memos.index'), route('attendance')] as $url) {
            $this->get($url)->assertRedirect(route('verification.notice'));
        }

        Volt::test('posts.comments', ['post' => $post])
            ->set('content', '댓글')
            ->call('add')
            ->assertRedirect(route('verification.notice'));

        Volt::test('posts.show', ['post' => $post])->call('vote', 1)->assertRedirect(route('verification.notice'));

        $this->assertSame(0, $post->comments()->count());
        $this->assertSame(0, $post->votes()->count());

        $this->get(route('contact'))->assertOk();
    }

    public function test_mailbox_variants_cannot_register_twice(): void
    {
        User::factory()->create(['email' => 'john.smith@gmail.com']);

        foreach (['johnsmith@gmail.com', 'j.o.h.n.smith+second@gmail.com', 'johnsmith@googlemail.com'] as $i => $email) {
            $this->register("dup{$i}", $email)->assertHasErrors('email');
        }

        $this->assertSame(1, User::count());
    }

    public function test_canonical_email_rules(): void
    {
        $this->assertSame('johnsmith@gmail.com', EmailAddress::canonical(' John.Smith+tag@GMAIL.com '));
        $this->assertSame('user@outlook.com', EmailAddress::canonical('user+news@outlook.com'));
        // Naver does not do sub-addressing, so "+" and dots are kept.
        $this->assertSame('a.b+c@naver.com', EmailAddress::canonical('A.B+c@naver.com'));
    }

    public function test_disposable_addresses_are_rejected(): void
    {
        $this->register('tempuser', 'someone@mailinator.com')->assertHasErrors('email');

        $this->assertSame(0, User::count());
    }

    public function test_only_three_signups_per_ip_per_hour(): void
    {
        Notification::fake();

        foreach (range(1, 3) as $i) {
            $this->register("user{$i}", "user{$i}@naver.com")->assertHasNoErrors();
            auth()->logout();
        }

        $this->register('user4', 'user4@naver.com')->assertHasErrors('username');

        $this->assertSame(3, User::count());
    }

    public function test_real_visitor_ip_is_read_through_cloudflare_and_the_proxy(): void
    {
        Route::get('/_ip', fn (Request $request) => $request->ip());

        // Cloudflare (172.69.x) -> Nginx Proxy Manager (192.168.0.33) -> app
        $this->get('/_ip', ['X-Forwarded-For' => '203.0.113.7, 172.69.195.56'], )
            ->assertSee('127.0.0.1'); // not from a trusted proxy: header ignored

        $this->withServerVariables(['REMOTE_ADDR' => '192.168.0.33'])
            ->get('/_ip', ['X-Forwarded-For' => '203.0.113.7, 172.69.195.56'])
            ->assertSee('203.0.113.7');

        // A visitor cannot spoof the header through the proxy chain with an untrusted hop.
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.0.33'])
            ->get('/_ip', ['X-Forwarded-For' => '1.1.1.1, 198.51.100.9'])
            ->assertSee('198.51.100.9');
    }
}
