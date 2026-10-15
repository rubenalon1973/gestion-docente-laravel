@props([
    'label',
    'table' => false,
])

<div {{ $attributes->class(['ui-dropdown', 'ui-dropdown-table' => $table]) }} data-dropdown @if ($table) data-dropdown-table @endif>
    <button class="ui-dropdown-trigger" type="button" data-dropdown-toggle aria-haspopup="menu" aria-expanded="false">
        {{ $label }}
        <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m6 8 4 4 4-4"></path></svg>
    </button>
    <div @class(['ui-dropdown-menu', 'ui-dropdown-menu-table' => $table]) data-dropdown-menu role="menu" hidden>
        {{ $slot }}
    </div>
</div>
