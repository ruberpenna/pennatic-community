<?php

namespace Tests\Feature\Auth;

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginFormSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_credential_forms_never_fall_back_to_get(): void
    {
        // If JavaScript fails, a GET submit would put the password in the URL.
        foreach (['/login', '/register', '/forgot-password'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('/<form method="post" wire:submit=/', $html, $url);
            $this->assertDoesNotMatchRegularExpression('/<form wire:submit=/', $html, $url);
        }
    }

    public function test_urls_use_https_when_the_site_is_configured_for_https(): void
    {
        config(['app.url' => 'https://example.test']);
        (new AppServiceProvider($this->app))->boot();

        $this->get('http://example.test/login')
            ->assertOk()
            ->assertSee('https://example.test/livewire', false)
            ->assertDontSee('http://example.test/livewire', false);
    }
}
