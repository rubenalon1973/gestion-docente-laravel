@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
    <div class="mb-6 flex flex-col items-center text-center">
        <x-ui.brand class="brand-lockup-header items-center" />
        <p class="mt-2 text-sm text-slate-500">Acceso interno</p>
    </div>

    <x-ui.form-errors />

    <x-ui.card title="Iniciar sesión" description="Introduce tus credenciales para acceder al panel.">
        <form class="section-stack" method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico</label>
                <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}"
                    autocomplete="username" autofocus required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input class="form-control" id="password" name="password" type="password"
                    autocomplete="current-password" required>
            </div>

            <x-ui.button class="w-full" type="submit">Entrar</x-ui.button>
        </form>
    </x-ui.card>

    <footer class="login-footer" aria-label="Información de acceso interno">
        <p class="login-footer-access">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
            <span>Acceso restringido · Uso interno</span>
        </p>
        <p>Proyecto personal de Rubén Alonso · 2026</p>
    </footer>
@endsection
