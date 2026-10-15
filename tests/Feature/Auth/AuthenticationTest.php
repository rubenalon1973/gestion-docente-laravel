<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('admin@example.com|127.0.0.1');

        parent::tearDown();
    }

    public function test_login_screen_is_public_and_registration_does_not_exist(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeText('Iniciar sesión')
            ->assertDontSee('Recordarme');

        $this->get('/register')->assertNotFound();
        $this->get('/up')->assertNotFound();
        $this->post('/_boost/browser-logs')->assertNotFound();
        $this->get('/storage/private-document.pdf')->assertNotFound();
    }

    public function test_guest_is_redirected_from_all_internal_areas(): void
    {
        foreach (['/', '/teachers', '/services', '/teachers/1/documents/1/download'] as $url) {
            $this->get($url)->assertRedirectToRoute('login');
        }
    }

    public function test_user_can_log_in_and_session_is_regenerated(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'ValidPassword!123',
        ]);
        $this->get(route('login'));
        $previousSessionId = session()->getId();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'ValidPassword!123',
        ]);

        $response->assertRedirectToRoute('teachers.index');
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($previousSessionId, session()->getId());
    }

    public function test_login_redirects_to_original_destination(): void
    {
        $user = User::factory()->create(['password' => 'ValidPassword!123']);
        $this->get(route('services.index'))->assertRedirectToRoute('login');

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'ValidPassword!123',
        ])->assertRedirectToRoute('services.index');
    }

    public function test_invalid_credentials_do_not_retain_password(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'incorrecta',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Las credenciales indicadas no son correctas.'])
            ->assertSessionHasInput('email', 'admin@example.com')
            ->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_failed_logins_are_rate_limited_by_email_and_ip(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => 'admin@example.com',
                'password' => 'incorrecta',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => 'admin@example.com',
            'password' => 'incorrecta',
        ])->assertSessionHasErrors('email');

        $this->assertStringContainsString(
            'Demasiados intentos de acceso.',
            session('errors')->first('email'),
        );
    }

    public function test_authenticated_user_is_redirected_away_from_login_and_can_log_out(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('login'))->assertRedirect('/');
        $this->post(route('logout'))->assertRedirectToRoute('login');
        $this->assertGuest();
    }
}
