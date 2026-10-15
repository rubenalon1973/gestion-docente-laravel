@props(['compact' => false])

<span {{ $attributes->class(['brand-lockup', 'brand-lockup-compact' => $compact]) }}>
    <span class="brand-collapsed" aria-hidden="true">G</span>
    <span class="brand-wordmark" aria-label="Gestión Docente">
        <span class="brand-name">GESTIÓN</span>
        <span class="brand-subname">DOCENTE</span>
    </span>
    <span class="brand-product">Panel de gestión de profesorado</span>
</span>
