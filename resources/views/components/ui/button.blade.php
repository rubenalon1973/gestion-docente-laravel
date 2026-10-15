@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $classes = [
        'inline-flex min-h-9 items-center justify-center rounded-lg border px-3 py-1.5 text-center text-[0.8125rem] font-semibold shadow-sm transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        'border-blue-700 bg-blue-700 text-white hover:border-blue-800 hover:bg-blue-800 focus-visible:outline-blue-600' => $variant === 'primary',
        'border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus-visible:outline-blue-600' => $variant === 'secondary',
        'border-red-200 bg-white text-red-700 hover:bg-red-50 focus-visible:outline-red-600' => $variant === 'danger',
        'border-transparent bg-transparent px-2 text-blue-700 shadow-none hover:bg-blue-50 focus-visible:outline-blue-600' => $variant === 'link',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" data-variant="{{ $variant }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" data-variant="{{ $variant }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
