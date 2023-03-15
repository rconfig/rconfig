<template>
    <main class="pf-c-page__main" tabindex="-1">
        <page-header :pagename="viewstate.pagename" :desc="viewstate.pagedesc"></page-header>

        <div class="pf-c-divider" role="separator"></div>

        <section class="pf-c-page__main-section pf-m-no-padding">
            <!-- Drawer -->
            <div class="pf-c-drawer" :class="{ 'pf-m-expanded': viewstate.openDrawerState }">
                <div class="pf-c-drawer__main">
                    <!-- Content -->
                    <div class="pf-c-drawer__content pf-m-no-background">
                        <div class="pf-c-drawer__body pf-m-padding">
                            <div class="pf-l-grid pf-m-gutter">
                                <div class="pf-l-grid__item pf-m-3-col">
                                    <settings-menu :viewstate="viewstate"> </settings-menu>
                                </div>
                                <div class="pf-l-grid__item pf-m-9-col">
                                    <router-view></router-view>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import useViewFunctions from '../composables/ViewFunctions';
import PageHeader from '../components/PageHeader.vue';
import SettingsMenu from './SettingsTabs/SettingsMenu.vue';

export default {
    props: {},
    components: { PageHeader, SettingsMenu },

    setup(props) {
        const viewstate = reactive({
            pagename: 'System Settings',
            pagedesc: 'Manage system settings and configuration',
            menubar: {
                0: {
                    name: 'System Overview',
                    pathname: '/settings/overview',
                    icon: 'fas fa-fw fa-search',
                    description: 'View system information and statistics',
                    active: false
                },
                1: {
                    name: 'System Settings',
                    pathname: '/settings/system',
                    icon: 'fas fa-fw fa-cogs',
                    description: 'Configure system banner, email and timezone settings',
                    active: false
                },
                2: {
                    name: 'Security',
                    pathname: '/settings/security',
                    icon: 'fas fa-fw fa-shield-alt',
                    description: 'View security settings',
                    active: false
                },
                3: {
                    name: 'About',
                    pathname: '/settings/about',
                    icon: 'pf pf-icon-help',
                    description: 'rConfig application information and support',
                    active: false
                }
            }
        });

        const { isLoading } = useViewFunctions(viewstate, viewstate.modelName, viewstate.modelObject);

        return { viewstate, isLoading };
    }
};
</script>
