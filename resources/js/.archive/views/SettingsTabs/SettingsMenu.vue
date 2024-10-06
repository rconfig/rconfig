<template>
    <div class="pf-c-menu">
        <div class="pf-c-menu__content">
            <ul class="pf-c-menu__list">
                <li class="pf-c-menu__list-item" v-for="(menu, index) in viewstate.menubar" :key="menu.name">
                    <button class="pf-c-menu__item" type="button" @click="settingsMenuSelect(menu, index)" :class="menu.active ? 'activeMenuItem' : ''">
                        <span class="pf-c-menu__item-main">
                            <span class="pf-c-menu__item-icon">
                                <i :class="menu.icon" aria-hidden="true"></i>
                            </span>
                            <span class="pf-c-menu__item-text">{{ menu.name }} </span>
                        </span>
                        <span class="pf-c-menu__item-description" style="white-space: pre-wrap; word-break: keep-all"
                            ><span>{{ menu.description }}</span></span
                        >
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

export default {
    props: {
        viewstate: {
            type: Object
        }
    },

    setup(props) {
        const route = useRoute();
        const router = useRouter();

        onMounted(() => {
            Object.entries(props.viewstate.menubar).forEach(([key, val]) => (val.pathname === route.path ? (val.active = true) : (val.active = false)));
        });

        function settingsMenuSelect(menu, index) {
            Object.entries(props.viewstate.menubar).forEach(([key, val]) => (val.active = false)); // foreach obect set active to false
            props.viewstate.menubar[index].active = true;
            router.push({ path: menu.pathname });
        }

        // watch(() => route.params, current, console.log(current));

        return { settingsMenuSelect };
    }
};
</script>

<style>
.activeMenuItem {
    background-color: #f5f5f5;
    border-left: 4px solid #007bff;
}
</style>
