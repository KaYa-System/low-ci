import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') {
        return;
    }

    // Force le thème clair en supprimant toujours la classe 'dark'
    document.documentElement.classList.remove('dark');
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();

    updateTheme(currentAppearance || 'system');
};

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Force toujours le thème clair
    updateTheme('light');
}

const appearance = ref<Appearance>('light');

export function useAppearance() {
    onMounted(() => {
        // Force toujours le thème clair
        appearance.value = 'light';
        updateTheme('light');
    });

    function updateAppearance(value: Appearance) {
        // Force toujours le thème clair, ignore les autres valeurs
        appearance.value = 'light';
        updateTheme('light');
    }

    return {
        appearance,
        updateAppearance,
    };
}
