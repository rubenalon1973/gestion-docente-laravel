const sidebar = document.querySelector('#sidebar');
const overlay = document.querySelector('#sidebar-overlay');
const openButton = document.querySelector('[data-sidebar-open]');
const closeButton = document.querySelector('[data-sidebar-close]');
const collapseButton = document.querySelector('[data-sidebar-collapse]');
const themeSwitchers = document.querySelectorAll('[data-theme-switcher]');
const dropdowns = document.querySelectorAll('[data-dropdown]');
const themeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
const themeStorageKey = 'teacher-management-theme';
const sidebarStorageKey = 'teacher-management-sidebar';
const themeLabels = {
    auto: 'Automático',
    light: 'Claro',
    dark: 'Oscuro',
};

const applyThemeChoice = (choice) => {
    const normalizedChoice = ['light', 'dark'].includes(choice) ? choice : 'auto';
    const resolvedTheme = normalizedChoice === 'auto'
        ? (themeMediaQuery.matches ? 'dark' : 'light')
        : normalizedChoice;

    document.documentElement.dataset.themeChoice = normalizedChoice;
    document.documentElement.dataset.theme = resolvedTheme;
    document.documentElement.style.colorScheme = resolvedTheme;

    themeSwitchers.forEach((switcher) => {
        const toggle = switcher.querySelector('[data-theme-toggle]');
        const label = themeLabels[normalizedChoice];

        toggle.setAttribute('aria-label', `Cambiar apariencia. Modo ${label.toLowerCase()}`);
        toggle.setAttribute('title', `Apariencia: ${label}`);
        switcher.querySelectorAll('[data-theme-icon]').forEach((icon) => {
            icon.toggleAttribute('hidden', icon.dataset.themeIcon !== normalizedChoice);
        });
        switcher.querySelectorAll('[data-theme-choice]').forEach((button) => {
            button.setAttribute('aria-checked', String(button.dataset.themeChoice === normalizedChoice));
        });
    });
};

const closeThemeMenus = () => {
    themeSwitchers.forEach((switcher) => {
        switcher.querySelector('[data-theme-menu]').hidden = true;
        switcher.querySelector('[data-theme-toggle]').setAttribute('aria-expanded', 'false');
    });
};

themeSwitchers.forEach((switcher) => {
    const toggle = switcher.querySelector('[data-theme-toggle]');
    const menu = switcher.querySelector('[data-theme-menu]');

    toggle.addEventListener('click', () => {
        const willOpen = menu.hidden;
        closeThemeMenus();
        menu.hidden = !willOpen;
        toggle.setAttribute('aria-expanded', String(willOpen));
    });

    switcher.querySelectorAll('[data-theme-choice]').forEach((button) => {
        button.addEventListener('click', () => {
            const choice = button.dataset.themeChoice;

            try {
                if (choice === 'auto') {
                    localStorage.removeItem(themeStorageKey);
                } else {
                    localStorage.setItem(themeStorageKey, choice);
                }
            } catch (_) {
                // El cambio sigue aplicándose durante la sesión actual.
            }

            applyThemeChoice(choice);
            closeThemeMenus();
            toggle.focus();
        });
    });
});

applyThemeChoice(document.documentElement.dataset.themeChoice ?? 'auto');

themeMediaQuery.addEventListener('change', () => {
    if (document.documentElement.dataset.themeChoice === 'auto') {
        applyThemeChoice('auto');
    }
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-theme-switcher]')) {
        closeThemeMenus();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeThemeMenus();
    }
});

const dropdownStates = Array.from(dropdowns).map((dropdown) => ({
    dropdown,
    toggle: dropdown.querySelector('[data-dropdown-toggle]'),
    menu: dropdown.querySelector('[data-dropdown-menu]'),
    portaled: false,
}));

const closeDropdowns = (returnFocus = false) => {
    dropdownStates.forEach((state) => {
        const { dropdown, menu, toggle } = state;

        if (!menu.hidden && returnFocus) {
            toggle.focus();
        }

        menu.hidden = true;
        menu.style.removeProperty('left');
        menu.style.removeProperty('right');
        menu.style.removeProperty('top');
        toggle.setAttribute('aria-expanded', 'false');

        if (state.portaled) {
            dropdown.append(menu);
            state.portaled = false;
        }
    });
};

const positionTableDropdown = (dropdown, toggle, menu) => {
    if (!dropdown.hasAttribute('data-dropdown-table')) {
        return;
    }

    const gap = 8;
    const viewportPadding = 8;
    const toggleRect = toggle.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();
    const availableBelow = window.innerHeight - toggleRect.bottom - viewportPadding;
    const openAbove = availableBelow < menuRect.height + gap && toggleRect.top > availableBelow;
    const preferredLeft = toggleRect.right - menuRect.width;
    const left = Math.min(
        Math.max(viewportPadding, preferredLeft),
        window.innerWidth - menuRect.width - viewportPadding,
    );
    const top = openAbove
        ? Math.max(viewportPadding, toggleRect.top - menuRect.height - gap)
        : Math.min(toggleRect.bottom + gap, window.innerHeight - menuRect.height - viewportPadding);

    menu.style.left = `${left}px`;
    menu.style.right = 'auto';
    menu.style.top = `${top}px`;
};

dropdownStates.forEach((state) => {
    const { dropdown, toggle, menu } = state;
    const items = Array.from(menu.querySelectorAll('[role="menuitem"]'));

    toggle.addEventListener('click', () => {
        const willOpen = menu.hidden;
        closeDropdowns();
        menu.hidden = !willOpen;
        toggle.setAttribute('aria-expanded', String(willOpen));

        if (willOpen) {
            if (dropdown.hasAttribute('data-dropdown-table')) {
                document.body.append(menu);
                state.portaled = true;
            }

            positionTableDropdown(dropdown, toggle, menu);
        }
    });

    toggle.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' && menu.hidden) {
            event.preventDefault();
            toggle.click();
            items[0]?.focus();
        }
    });

    menu.addEventListener('keydown', (event) => {
        const currentIndex = items.indexOf(document.activeElement);

        if (event.key === 'Escape') {
            event.preventDefault();
            closeDropdowns(true);
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            items[(currentIndex + 1) % items.length]?.focus();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            items[(currentIndex - 1 + items.length) % items.length]?.focus();
        } else if (event.key === 'Home') {
            event.preventDefault();
            items[0]?.focus();
        } else if (event.key === 'End') {
            event.preventDefault();
            items.at(-1)?.focus();
        }
    });
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-dropdown]')) {
        closeDropdowns();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeDropdowns(true);
    }
});

window.addEventListener('resize', () => closeDropdowns());
document.addEventListener('scroll', () => closeDropdowns(), true);

if (collapseButton) {
    const applySidebarState = (collapsed, persist = true) => {
        document.documentElement.dataset.sidebar = collapsed ? 'collapsed' : 'expanded';
        collapseButton.setAttribute('aria-expanded', String(!collapsed));
        collapseButton.querySelector('[data-sidebar-collapse-label]').textContent = collapsed
            ? 'Desplegar navegación'
            : 'Plegar navegación';

        if (persist) {
            try {
                localStorage.setItem(sidebarStorageKey, collapsed ? 'collapsed' : 'expanded');
            } catch (_) {
                // El estado sigue aplicándose durante la sesión actual.
            }
        }
    };

    applySidebarState(document.documentElement.dataset.sidebar === 'collapsed', false);
    collapseButton.addEventListener('click', () => {
        applySidebarState(document.documentElement.dataset.sidebar !== 'collapsed');
    });
}

if (sidebar && overlay && openButton && closeButton) {
    let previousFocus = null;
    const pageShell = document.querySelector('.app-shell');
    const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    const setSidebarOpen = (isOpen) => {
        sidebar.dataset.open = String(isOpen);
        overlay.dataset.open = String(isOpen);
        openButton.setAttribute('aria-expanded', String(isOpen));
        document.body.classList.toggle('overflow-hidden', isOpen);
        pageShell?.toggleAttribute('inert', isOpen);
        pageShell?.setAttribute('aria-hidden', String(isOpen));

        if (isOpen) {
            previousFocus = document.activeElement;
            closeButton.focus();
        } else if (previousFocus instanceof HTMLElement) {
            previousFocus.focus();
        }
    };

    openButton.addEventListener('click', () => setSidebarOpen(true));
    closeButton.addEventListener('click', () => setSidebarOpen(false));
    overlay.addEventListener('click', () => setSidebarOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.dataset.open === 'true') {
            setSidebarOpen(false);
        } else if (event.key === 'Tab' && sidebar.dataset.open === 'true') {
            const focusableItems = Array.from(sidebar.querySelectorAll(focusableSelector))
                .filter((element) => !element.hasAttribute('hidden'));
            const firstItem = focusableItems[0];
            const lastItem = focusableItems.at(-1);

            if (event.shiftKey && document.activeElement === firstItem) {
                event.preventDefault();
                lastItem?.focus();
            } else if (!event.shiftKey && document.activeElement === lastItem) {
                event.preventDefault();
                firstItem?.focus();
            }
        }
    });
}
