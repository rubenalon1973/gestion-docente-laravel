<div {{ $attributes->class('ui-table max-w-full overflow-x-auto rounded-lg border border-slate-200 [overscroll-behavior-inline:contain]') }} tabindex="0" role="region" aria-label="Tabla desplazable">
    <table class="data-table">
        {{ $slot }}
    </table>
</div>
