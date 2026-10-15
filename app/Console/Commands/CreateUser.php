<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('user:create')]
#[Description('Crea un usuario interno de forma interactiva')]
class CreateUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = (string) $this->ask('Nombre');
        $email = (string) $this->ask('Correo electrónico');
        $password = $this->secret('Contraseña');
        $confirmation = $this->secret('Confirma la contraseña');

        if ($password !== $confirmation) {
            $this->error('Las contraseñas no coinciden.');

            return Command::FAILURE;
        }

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
            ],
            [
                'name.required' => 'El nombre es obligatorio.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico no es válido.',
                'email.unique' => 'Ya existe un usuario con ese correo electrónico.',
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

        User::query()->create($validator->validated());
        $this->info('Usuario creado correctamente.');

        return Command::SUCCESS;
    }
}
