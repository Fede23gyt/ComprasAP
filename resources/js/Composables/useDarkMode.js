import { ref, watchEffect } from 'vue';

export function useDarkMode() {
    const isDark = ref(localStorage.getItem('dark') === 'true');

    watchEffect(() => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('dark', 'true');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('dark', 'false');
        }
    });

    function toggle() {
        isDark.value = !isDark.value;
    }

    return { isDark, toggle };
}
