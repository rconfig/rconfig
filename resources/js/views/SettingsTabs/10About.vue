<template>
    <div class="pf-c-panel pf-m-raised">
        <div class="pf-c-panel__header">About</div>
        <hr class="pf-c-divider" />
        <div class="pf-c-panel__main">
            <div class="pf-c-panel__main-body">
                <div class="pf-l-flex" v-if="state.isLoading" style="justify-content: center">
                    <div class="pf-l-flex__item">
                        <data-table-spinner v-if="state.isLoading"></data-table-spinner>
                    </div>
                </div>
                <div class="pf-m-gutter" v-if="!state.isLoading">
                    <div class="pf-c-card">
                        <div class="pf-c-card__title">
                            <h2 class="pf-c-title pf-m-xl">License and Support Information</h2>
                        </div>
                        <div class="pf-c-card__body">
                            <dl class="pf-c-description-list">
                                <div class="pf-c-description-list__group">
                                    <dt class="pf-c-description-list__term">
                                        <span class="pf-c-description-list__text">Version </span>
                                    </dt>
                                    <dd class="pf-c-description-list__description">
                                        <div class="pf-c-description-list__text">{{ state.info.version ? state.info.version : 'No information found!' }}</div>
                                    </dd>
                                </div>
                                <div class="pf-c-description-list__group">
                                    <dt class="pf-c-description-list__term">
                                        <span class="pf-c-description-list__text">License ID</span>
                                    </dt>
                                    <dd class="pf-c-description-list__description">
                                        <div class="pf-c-description-list__text">{{ state.info.rconfig_sub_id ? state.info.rconfig_sub_id : 'No information found!' }}</div>
                                    </dd>
                                </div>
                                <div class="pf-c-description-list__group">
                                    <dt class="pf-c-description-list__term">
                                        <span class="pf-c-description-list__text">Licensee Name</span>
                                    </dt>
                                    <dd class="pf-c-description-list__description">
                                        <div class="pf-c-description-list__text">{{ state.info.rconfig_sub_name ? state.info.rconfig_sub_name : 'No information found!' }}</div>
                                    </dd>
                                </div>
                                <div class="pf-c-description-list__group">
                                    <dt class="pf-c-description-list__term">
                                        <span class="pf-c-description-list__text">License Status</span>
                                    </dt>
                                    <dd class="pf-c-description-list__description">
                                        <div class="pf-c-description-list__text">{{ state.info.rconfig_sub_status ? state.info.rconfig_sub_status : 'No information found!' }}</div>
                                    </dd>
                                </div>
                                <div class="pf-c-description-list__group">
                                    <dt class="pf-c-description-list__term">
                                        <span class="pf-c-description-list__text">License Expiry</span>
                                    </dt>
                                    <dd class="pf-c-description-list__description">
                                        <div class="pf-c-description-list__text">{{ state.info.rconfig_sub_exiry ? state.info.rconfig_sub_exiry : 'No information found!' }}</div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <hr class="pf-c-divider" />
                        <div class="pf-c-card__footer">
                            <a href="https://www.rconfig.com/eula" target="_blank">License</a> |
                            <a :href="'mailto:support@rconfig.com?subject=rConfig Support from ' + state.info.rconfig_sub_name">Contact Support</a> |
                            <a href="https://docs.rconfig.com/" target="_blank">Online Help</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted, reactive, inject } from 'vue';
import DataTableSpinner from '../../components/DataTableSpinner.vue';

export default {
    props: {},
    components: {
        DataTableSpinner
    },
    setup(props) {
        const state = reactive({
            isLoading: false,
            error: null,
            info: {
                version: '',
                rconfig_sub_id: '',
                rconfig_sub_name: '',
                rconfig_sub_status: '',
                rconfig_sub_exiry: ''
            }
        });
        const createNotification = inject('create-notification');

        onMounted(() => {
            state.isLoading = true;
            getInfo();
        });

        function getInfo() {
            axios
                .get('/api/settings/support-info')
                .then((response) => {
                    Object.assign(state.info, response.data.data);
                    state.isLoading = false;
                    state.error = null;
                })
                .catch((error) => {
                    createNotification({
                        type: 'error',
                        message: error.response.data.message
                    });
                });
        }

        return { state };
    }
};
</script>
