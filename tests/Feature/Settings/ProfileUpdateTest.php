<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($user = User::factory()->create());

        $this->get('/settings/profile')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('settings.profile')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_intro_and_avatar_can_be_updated(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('settings.profile')
            ->set('intro', '안녕하세요')
            ->set('avatar', UploadedFile::fake()->image('me.png', 200, 200))
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('안녕하세요', $user->intro);
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);

        $oldPath = $user->avatar_path;

        Volt::test('settings.profile')->call('removeAvatar');

        $this->assertNull($user->refresh()->avatar_path);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_avatar_must_be_an_image(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Volt::test('settings.profile')
            ->set('avatar', UploadedFile::fake()->create('shell.php', 10, 'application/x-php'))
            ->call('updateProfileInformation')
            ->assertHasErrors('avatar');
    }

    public function test_nickname_must_be_unique(): void
    {
        User::factory()->create(['name' => '운영자']);

        $this->actingAs(User::factory()->create());

        Volt::test('settings.profile')
            ->set('name', '운영자')
            ->call('updateProfileInformation')
            ->assertHasErrors('name');
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('settings.profile')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $response
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertSoftDeleted($user);
        $this->assertFalse(auth()->check());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('settings.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $response->assertHasErrors(['password']);

        $this->assertNotNull($user->fresh());
    }
}
