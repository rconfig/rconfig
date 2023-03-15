// Sets state for side nav bar
import { ref, reactive } from 'vue';

const globalState = reactive({
    navState: null,
    darkmode: false
});
const darkmode = ref(false);

export const useNavState = () => {
    const localState = reactive({
        darkmode: false
    });

    const changeNavState = () => {
        // if (window.innerWidth < 1200) {
        //     alert('Less than 1200');
        // }

        if ((globalState.navState === null || globalState.navState === 'pf-m-expanded') && window.innerWidth >= 1200) {
            globalState.navState = 'pf-m-collapsed';
            return;
        }
        if (globalState.navState === 'pf-m-collapsed' && window.innerWidth >= 1200) {
            globalState.navState = 'pf-m-expanded';
            return;
        }
        if (globalState.navState === null && window.innerWidth < 1200) {
            globalState.navState = 'pf-m-expanded';
            return;
        }
        if (globalState.navState === 'pf-m-expanded') {
            globalState.navState = null;
            return;
        }
    };

    const toggleDarkMode = () => {
        //https://lukelowrey.com/css-variable-theme-switcher/
        // var storedTheme = localStorage.getItem('rconfig-theme') || (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
        var storedTheme = localStorage.getItem('theme');
        if (storedTheme) {
            console.log('storedTheme', storedTheme);
            document.documentElement.setAttribute('data-theme', storedTheme);
        }

        var currentTheme = document.documentElement.getAttribute('data-theme');
        var targetTheme = 'light';

        if (currentTheme === 'light') {
            targetTheme = 'dark';
        }

        document.documentElement.setAttribute('data-theme', targetTheme);
        localStorage.setItem('theme', targetTheme);
        darkmode.value = !darkmode.value;
    };

    return {
        darkmode,
        globalState,
        localState,
        changeNavState,
        toggleDarkMode
    };
};
