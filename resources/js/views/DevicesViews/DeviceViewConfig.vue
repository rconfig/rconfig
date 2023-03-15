<template>
    <main class="pf-c-page__main" tabindex="-1">
        <page-header :pagename="viewstate.pagename">
            <template v-slot:breadcrumbs> <devices-breadcrumbs :devicename="configModel.device_name" :deviceId="configModel.device_id"></devices-breadcrumbs></template>
        </page-header>
        <div class="pf-c-divider" role="separator"></div>

        <section class="pf-c-page__main-section pf-m-no-padding">
            <!-- Drawer -->
            <div class="pf-c-drawer" :class="{ 'pf-m-expanded': viewstate.openDrawerState }" id="top_div">
                <div class="pf-c-drawer__main">
                    <!-- Content -->
                    <div class="pf-c-drawer__content pf-m-no-background">
                        <div class="pf-c-drawer__body pf-m-padding">
                            <loading-spinner :showSpinner="viewstate.isLoading"></loading-spinner>

                            <div class="pf-l-grid pf-m-gutter" v-if="!viewstate.isLoading">
                                <div class="pf-l-grid__item pf-m-12-col pf-m-3-col-on-md">
                                    <device-view-config-details-panel :configModel="configModel"></device-view-config-details-panel>
                                </div>

                                <div class="pf-l-grid__item pf-m-12-col pf-m-9-col-on-md">
                                    <monaco-code-panel
                                        :config_id="config_id"
                                        :viewstate="viewstate"
                                        :configModel="configModel"
                                        @showConfigFullScreen="showConfigFullScreen($event)"
                                    ></monaco-code-panel>
                                </div>
                            </div>
                            <div class="pf-c-back-to-top" v-if="showScrollBtn">
                                <button class="pf-c-button pf-m-primary" @click="scrollToTop">
                                    Back to top
                                    <span class="pf-c-button__icon pf-m-end">
                                        <i class="fas fa-angle-up" aria-hidden="true"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <config-full-screen-view
        v-if="viewstate.configFullScreen"
        @closeModal="viewstate.configFullScreen = false"
        :editid="viewstate.editid"
        :code="viewstate.modelObject.code"
        :filename="viewstate.modelObject.filename"
        :language="viewstate.modelObject.language"
    ></config-full-screen-view>
</template>

<script>
// import DeviceViewConfigCodePanel from './components/DeviceViewConfigCodePanel.vue';
import ConfigFullScreenView from '../../components/ConfigFullScreenView.vue';
import DeviceViewConfigDetailsPanel from './components/DeviceViewConfigDetailsPanel.vue';
import DevicesBreadcrumbs from '../../components/DevicesBreadcrumbs.vue';
import LoadingSpinner from '../../components/LoadingSpinner.vue';
import MonacoCodePanel from '../../components/MonacoCodePanel.vue';
import PageHeader from '../../components/PageHeader.vue';
import SideDrawer from '../../components/SideDrawer.vue';
import useViewFunctions from '../../composables/ViewFunctions';
import { ref, reactive, onUnmounted, onMounted, inject } from 'vue';
import { useRoute } from 'vue-router';

export default {
    props: {},

    components: { ConfigFullScreenView, DevicesBreadcrumbs, PageHeader, LoadingSpinner, MonacoCodePanel, DeviceViewConfigDetailsPanel, SideDrawer },

    setup(props) {
        const route = useRoute();
        const config_id = route.params.id;
        const configModel = reactive({});
        const viewstate = reactive({
            editid: 0,
            pagename: 'Configuration',
            pagedesc: 'View your downloaded configuration file',
            pagenamesingle: 'config',
            modelName: 'configs',
            isLoading: false,
            modelObject: {
                code: ''
            }
        });

        const showScrollBtn = ref(false);

        const createNotification = inject('create-notification');

        onMounted(() => {
            getConfigModel();
            window.addEventListener('wheel', handleScroll, { passive: true });
        });

        onUnmounted(() => {
            window.removeEventListener('wheel', handleScroll);
        });

        function getConfigModel() {
            viewstate.isLoading = true;
            axios
                .get('/api/configs/' + config_id)
                .then((response) => {
                    Object.assign(configModel, response.data);
                    viewstate.isLoading = false;
                })
                .catch((error) => {
                    createNotification({
                        type: 'danger',
                        title: 'Error',
                        message: error.response
                    });
                    viewstate.isLoading = false;
                });
        }

        function showConfigFullScreen(event) {
            viewstate.modelObject.code = event.code;
            viewstate.modelObject.filename = event.filename;
            viewstate.modelObject.language = 'default';
            viewstate.configFullScreen = true;
        }

        const handleScroll = (e) => {
            let myScroll = document.getElementById('top_div').getBoundingClientRect().top;
            if (myScroll < 0) {
                showScrollBtn.value = true;
            } else {
                showScrollBtn.value = false;
            }
        };

        function scrollToTop() {
            document.getElementById('top_div').scrollIntoView({ behavior: 'smooth' });
        }

        return {
            configModel,
            config_id,
            scrollToTop,
            showScrollBtn,
            showConfigFullScreen,
            viewstate
        };
    }
};
</script>
