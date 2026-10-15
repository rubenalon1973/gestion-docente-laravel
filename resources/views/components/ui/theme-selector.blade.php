<div {{ $attributes->class('theme-switcher') }} data-theme-switcher>
    <button class="theme-switcher-trigger" type="button" data-theme-toggle aria-haspopup="menu" aria-expanded="false"
        aria-label="Cambiar apariencia. Modo automático" title="Apariencia: Automático">
        <svg class="theme-switcher-icon" data-theme-icon="auto" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="3" y="4" width="18" height="13" rx="2"></rect>
            <path d="M8 21h8M12 17v4"></path>
        </svg>
        <svg class="theme-switcher-icon" data-theme-icon="light" viewBox="0 0 24 24" aria-hidden="true" hidden>
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"></path>
        </svg>
        <svg class="theme-switcher-icon" data-theme-icon="dark" viewBox="0 0 24 24" aria-hidden="true" hidden>
            <path d="M20.5 14.2A8 8 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z"></path>
        </svg>
    </button>

    <div class="theme-switcher-menu" data-theme-menu role="menu" aria-label="Tema visual" hidden>
        <button type="button" role="menuitemradio" data-theme-choice="auto">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="13" rx="2"></rect><path d="M8 21h8M12 17v4"></path></svg>
            Automático
        </button>
        <button type="button" role="menuitemradio" data-theme-choice="light">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"></path></svg>
            Claro
        </button>
        <button type="button" role="menuitemradio" data-theme-choice="dark">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.2A8 8 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z"></path></svg>
            Oscuro
        </button>
    </div>
</div>
