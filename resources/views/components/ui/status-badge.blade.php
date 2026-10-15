@props(['status'])

@php
    [$label, $classes] = match ($status) {
        'active' => ['Activo', 'bg-emerald-100 text-emerald-800'],
        'inactive' => ['Inactivo', 'bg-amber-100 text-amber-800'],
        'archived' => ['Archivado', 'bg-slate-200 text-slate-700'],
        default => [$status, 'bg-slate-100 text-slate-700'],
    };
@endphp

<span data-status="{{ $status }}" {{ $attributes->class(['ui-status-badge inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', $classes]) }}>{{ $label }}</span>
