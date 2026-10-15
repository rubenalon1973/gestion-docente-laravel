@props(['title' => null, 'description' => null])

<section {{ $attributes->class('ui-card rounded-xl border border-slate-200 bg-white shadow-sm') }}>
    @if ($title || $description || isset($actions))
        <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-start sm:justify-between sm:px-5">
            <div>
                @if ($title)
                    <h2 class="text-lg font-semibold text-slate-900">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-col gap-2 sm:shrink-0 sm:flex-row">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div class="p-4 sm:p-5">
        {{ $slot }}
    </div>
</section>
