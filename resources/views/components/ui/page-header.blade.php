@props(['title', 'description' => null])

<header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1.5 max-w-3xl text-sm text-slate-500">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-col gap-2 sm:shrink-0 sm:flex-row sm:flex-wrap">{{ $actions }}</div>
    @endisset
</header>
