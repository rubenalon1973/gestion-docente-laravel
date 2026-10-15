<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCommandsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_create_user_command_requests_hidden_confirmed_password(): void
    {
        $this->artisan('user:create')
            ->expectsQuestion('Nombre', 'Administración')
            ->expectsQuestion('Correo electrónico', 'admin@example.com')
            ->expectsQuestion('Contraseña', 'SecurePassword!123')
            ->expectsQuestion('Confirma la contraseña', 'SecurePassword!123')
            ->expectsOutput('Usuario creado correctamente.')
            ->assertSuccessful();

        $user = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $this->assertSame('Administración', $user->name);
        $this->assertTrue(Hash::check('SecurePassword!123', $user->password));
        $this->assertNotSame('SecurePassword!123', $user->password);
    }

    public function test_reset_password_command_updates_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'PreviousPassword!123',
        ]);

        $this->artisan('user:reset-password')
            ->expectsQuestion('Correo electrónico del usuario', 'admin@example.com')
            ->expectsQuestion('Nueva contraseña', 'NewSecurePassword!123')
            ->expectsQuestion('Confirma la nueva contraseña', 'NewSecurePassword!123')
            ->expectsOutput('Contraseña restablecida correctamente.')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('NewSecurePassword!123', $user->fresh()->password));
    }

    public function test_commands_reject_unconfirmed_password_and_unknown_user(): void
    {
        $this->artisan('user:create')
            ->expectsQuestion('Nombre', 'Administración')
            ->expectsQuestion('Correo electrónico', 'admin@example.com')
            ->expectsQuestion('Contraseña', 'SecurePassword!123')
            ->expectsQuestion('Confirma la contraseña', 'DifferentPassword!123')
            ->expectsOutputToContain('Las contraseñas no coinciden.')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);

        $this->artisan('user:reset-password')
            ->expectsQuestion('Correo electrónico del usuario', 'missing@example.com')
            ->expectsOutput('No existe ningún usuario con ese correo electrónico.')
            ->assertFailed();
    }
}
