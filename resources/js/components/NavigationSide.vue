<template>
    <div class="pf-c-page__sidebar" :class="globalState.navState">
        <div class="pf-c-page__sidebar-body" style="padding-top: 0px !important">
            <nav class="pf-c-nav" id="primary-detail-panel-body-padding-primary-nav" aria-label="Global">
                <ul class="pf-c-nav__list">
                    <li
                        v-for="menuItem in menu.items"
                        :key="menuItem.id"
                        class="pf-c-nav__item pf-m-expandable"
                        :class="[
                            menuItem.isactive === true && !menuItem.hasOwnProperty('subitems') ? 'pf-m-current' : '',
                            menuItem.isactive === true && menuItem.hasOwnProperty('subitems') ? 'pf-m-current pf-m-expanded' : ''
                        ]"
                    >
                        <router-link v-if="!menuItem.subitems" tag="li" class="pf-c-nav__item" :to="menuItem.route" @click="toggleMainMenu(menuItem)">
                            <a class="pf-c-nav__link"> {{ menuItem.name }} </a>
                        </router-link>

                        <button class="pf-c-nav__link" @click="toggleMainMenu(menuItem)" v-if="menuItem.subitems">
                            {{ menuItem.name }}
                            <span class="pf-c-nav__toggle" v-if="menuItem.subitems">
                                <span class="pf-c-nav__toggle-icon">
                                    <i class="fas fa-angle-right" aria-hidden="true"></i>
                                </span>
                            </span>
                        </button>
                        <transition name="slide">
                            <section class="pf-c-nav__subnav" v-if="menuItem.isactive === true && menuItem.hasOwnProperty('subitems')">
                                <ul class="pf-c-nav__list" v-for="subItem in menuItem.subitems" :key="subItem.name">
                                    <li class="pf-c-nav__item">
                                        <a class="pf-c-nav__link" v-if="subItem.hasOwnProperty('isWebPath') && subItem.isWebPath === true" :href="subItem.route" target="_blank">
                                            {{ subItem.name }}
                                        </a>
                                        <router-link tag="li" v-else class="pf-c-nav__link" :to="subItem.route" @click="toggleSubMenu(menuItem, subItem)">
                                            {{ subItem.name }}
                                        </router-link>
                                    </li>
                                </ul>
                            </section>
                        </transition>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</template>

<script>
import { reactive, computed, onMounted } from 'vue';
import { useNavState } from '../composables/navstate';
import { useRoute, useRouter } from 'vue-router';

export default {
    props: {},

    setup(props) {
        const route = useRoute();
        const router = useRouter();
        const currentRoute = computed(() => {
            return route.path;
        });

        const menu = reactive({
            items: [
                {
                    id: 1,
                    name: 'Dashboard',
                    route: '/dashboard',
                    isactive: false
                },
                {
                    id: 2,
                    name: 'Devices',
                    route: '/devices',
                    isactive: false,
                    subitems: [
                        {
                            name: 'Devices',
                            route: '/devices',
                            isactive: false
                        },
                        {
                            name: 'Templates',
                            route: '/templates',
                            isactive: false
                        },
                        {
                            name: 'Categories',
                            route: '/categories',
                            isactive: false
                        },
                        {
                            name: 'Commands',
                            route: '/commands',
                            isactive: false
                        },
                        {
                            name: 'Vendors',
                            route: '/vendors',
                            isactive: false
                        },
                        {
                            name: 'Tags',
                            route: '/tags',
                            isactive: false
                        }
                    ]
                },
                {
                    id: 3,
                    name: 'Scheduled Tasks',
                    route: '/scheduled-tasks',
                    isactive: false
                },
                {
                    id: 4,
                    name: 'Configurations',
                    route: '/configurations',
                    isactive: false,
                    subitems: [
                        {
                            name: 'Search',
                            route: '/config-search',
                            isactive: false
                        },
                        {
                            name: 'Reports',
                            route: '/config-reports',
                            isactive: false
                        },
                    ]
                },
                {
                    id: 6,
                    name: 'Settings',
                    route: '/settings',
                    isactive: false,
                    subitems: [
                        {
                            name: 'Settings',
                            route: '/settings/overview',
                            isactive: false
                        },
                        {
                            name: 'Users',
                            route: '/settings/users',
                            isactive: false
                        },
                        {
                            name: 'Activity Logs',
                            route: '/settings/activitylog',
                            isactive: false
                        },
                        {
                            name: 'Queue Manager',
                            route: '/horizon/dashboard',
                            isWebPath: true
                        }
                    ]
                }
            ]
        });

        function toggleMainMenu(menuItem) {
            menu.items.forEach((item) => {
                item.isactive = false;
            });

            menuItem.isactive = !menuItem.isactive;
        }

        function toggleSubMenu(menuItem, subItem) {
            menuItem.isactive = true;
        }

        onMounted(async () => {
            await router.isReady();
            menu.items.forEach((item) => {
                if (item.route === currentRoute.value) {
                    item.isactive = true;
                    return;
                }

                if (item.subitems) {
                    item.subitems.forEach((subItem) => {
                        if (subItem.route === currentRoute.value) {
                            item.isactive = true;
                            subItem.isactive = true;
                            return;
                        }
                    });
                }
            });
        });

        const { globalState, localState, changeNavState } = useNavState();

        return { menu, globalState, localState, changeNavState, toggleMainMenu, toggleSubMenu };
    }
};
</script>

<style>
/* NavigationSide Transition Slide Styles */
.slide-enter-active {
    -moz-transition-duration: 0.1s;
    -webkit-transition-duration: 0.1s;
    -o-transition-duration: 0.1s;
    transition-duration: 0.1s;
    -moz-transition-timing-function: ease-in;
    -webkit-transition-timing-function: ease-in;
    -o-transition-timing-function: ease-in;
    transition-timing-function: ease-in;
}
.slide-leave-active {
    -moz-transition-duration: 0.1s;
    -webkit-transition-duration: 0.1s;
    -o-transition-duration: 0.1s;
    transition-duration: 0.1s;
    -moz-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
    -webkit-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
    -o-transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
    transition-timing-function: cubic-bezier(0, 1, 0.5, 1);
}

.slide-enter-to,
.slide-leave-from {
    max-height: 100px;
    overflow: hidden;
}

.slide-enter-from,
.slide-leave-to {
    overflow: hidden;
    max-height: 0;
}
/* END NavigationSide Transition Slide Styles */
</style>
