<script>
    (() => {
        const storageKey = 'teacher-management-theme';
        let choice = 'auto';

        try {
            const storedChoice = localStorage.getItem(storageKey);
            if (storedChoice === 'light' || storedChoice === 'dark') {
                choice = storedChoice;
            }
        } catch (_) {
            // El modo automático sigue disponible si el almacenamiento está bloqueado.
        }

        const theme = choice === 'auto'
            ? (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
            : choice;

        document.documentElement.dataset.themeChoice = choice;
        document.documentElement.dataset.theme = theme;
        document.documentElement.style.colorScheme = theme;

        try {
            document.documentElement.dataset.sidebar = localStorage.getItem('teacher-management-sidebar') === 'collapsed'
                ? 'collapsed'
                : 'expanded';
        } catch (_) {
            document.documentElement.dataset.sidebar = 'expanded';
        }
    })();
</script>
