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
    <div class="fixed right-4 top-4 z-10 sm:right-6 sm:top-6">
        <x-ui.theme-selector />
    </div>
    <main class="flex min-h-screen items-center justify-center px-4 py-24 sm:py-10">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </main>
</body>
</html>
