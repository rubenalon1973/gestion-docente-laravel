<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Gestión Docente')</title>
    <x-ui.theme-init />
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <a class="skip-link" href="#contenido-principal">Saltar al contenido principal</a>

    <div id="sidebar-overlay" class="sidebar-overlay" data-open="false" aria-hidden="true"></div>

    <aside id="sidebar" class="sidebar flex flex-col" data-open="false" aria-label="Navegación principal">
        <div class="relative flex min-h-20 items-center border-b border-white/10 px-5 py-3 pr-16">
            <a href="{{ route('teachers.index') }}" class="min-w-0 rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                <x-ui.brand compact />
            </a>
            <button type="button" class="absolute right-3 top-3 inline-flex size-11 items-center justify-center rounded-lg text-slate-200 hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white lg:hidden"
                data-sidebar-close aria-label="Cerrar menú">
                <svg class="size-6 fill-none stroke-current stroke-2" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </div>

        <nav class="flex-1 space-y-2 p-4">
            <x-ui.nav-link href="{{ route('teachers.index') }}" :active="request()->routeIs('teachers.*')" aria-label="Profesores" title="Profesores">
                <svg class="sidebar-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <span class="sidebar-nav-label">Profesores</span>
            </x-ui.nav-link>
            <x-ui.nav-link href="{{ route('services.index') }}" :active="request()->routeIs('services.*')" aria-label="Servicios" title="Servicios">
                <svg class="sidebar-nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 3 7l9 5 9-5-9-5Z"></path><path d="m3 12 9 5 9-5M3 17l9 5 9-5"></path></svg>
                <span class="sidebar-nav-label">Servicios</span>
            </x-ui.nav-link>
        </nav>
    </aside>

    <div class="app-shell min-h-screen min-w-0">
        <header class="app-header sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
            <div class="mobile-header-layout min-h-16 px-4 py-2 sm:px-6 lg:px-8">
                <div class="mobile-header-brand min-w-0 lg:hidden">
                    <x-ui.brand compact class="brand-lockup-header" />
                </div>
                <div class="mobile-header-controls lg:contents">
                    <button type="button" class="mobile-menu-button lg:hidden"
                        data-sidebar-open aria-controls="sidebar" aria-expanded="false">
                        Menú
                    </button>
                <button class="sidebar-collapse-button hidden lg:inline-flex" type="button" data-sidebar-collapse aria-controls="sidebar" aria-expanded="true">
                    <span class="sr-only" data-sidebar-collapse-label>Plegar navegación</span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg>
                </button>
                <p class="hidden text-sm font-medium text-slate-500 lg:block">Panel de gestión</p>
                <div class="ml-auto flex shrink-0 items-center gap-2 sm:gap-3">
                    <x-ui.theme-selector />
                    <span class="hidden text-sm text-slate-600 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-button" aria-label="Cerrar sesión" title="Cerrar sesión">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"></path><path d="M14 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path></svg>
                            <span class="hidden lg:inline">Cerrar sesión</span>
                        </button>
                    </form>
                </div>
                </div>
            </div>
        </header>

        <main id="contenido-principal" class="mx-auto w-full min-w-0 max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8" tabindex="-1">
            @if (session('success'))
                <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
            @endif
            @if (session('error'))
                <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
