<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('user:reset-password')]
#[Description('Restablece de forma interactiva la contraseña de un usuario interno')]
class ResetUserPassword extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) $this->ask('Correo electrónico del usuario');
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error('No existe ningún usuario con ese correo electrónico.');

            return Command::FAILURE;
        }

        $password = $this->secret('Nueva contraseña');
        $confirmation = $this->secret('Confirma la nueva contraseña');

        if ($password !== $confirmation) {
            $this->error('Las contraseñas no coinciden.');

            return Command::FAILURE;
        }

        $validator = Validator::make(
            compact('password'),
            ['password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()]],
            [
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 12 caracteres.',
                'password.mixed' => 'La contraseña debe incluir mayúsculas y minúsculas.',
                'password.numbers' => 'La contraseña debe incluir al menos un número.',
                'password.symbols' => 'La contraseña debe incluir al menos un símbolo.',
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return Command::FAILURE;
        }

        $user->update(['password' => $password]);
        $this->info('Contraseña restablecida correctamente.');

        return Command::SUCCESS;
    }
}
