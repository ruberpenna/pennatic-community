<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PasswordMustChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_who_must_change_password_is_redirected(): void
    {
        $user = User::factory()->create(['password_must_change' => true]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('settings.password'));

        $this->actingAs($user)
            ->get(route('settings.profile'))
            ->assertRedirect(route('settings.password'));
    }

    public function test_user_who_must_change_password_can_open_password_page_and_log_out(): void
    {
        $user = User::factory()->create(['password_must_change' => true]);

        $this->actingAs($user)
            ->get(route('settings.password'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_changing_password_clears_the_flag(): void
    {
        $user = User::factory()->create(['password_must_change' => true]);

        $this->actingAs($user);

        Volt::test('settings.password')
            ->set('current_password', 'password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertFalse($user->refresh()->password_must_change);

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_other_users_are_not_redirected(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk();
    }
}
