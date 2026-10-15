@props(['href', 'active' => false])

<a href="{{ $href }}"
    {{ $attributes->class([
        'sidebar-nav-link flex min-h-10 items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-[3px]',
        'sidebar-nav-link-active shadow-sm' => $active,
        'sidebar-nav-link-idle' => ! $active,
    ])->merge(['aria-current' => $active ? 'page' : null]) }}>
    {{ $slot }}
</a>
