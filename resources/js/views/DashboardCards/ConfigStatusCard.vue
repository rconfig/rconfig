<template>
    <div class="pf-c-card">
        <div class="pf-c-card__header">
            <div class="pf-c-card__actions">
                <button class="pf-c-button pf-m-plain" @click="refreshData()">
                    <span class="pf-c-button__icon">
                        <i class="fas fa-redo" aria-hidden="true" v-if="!isRefreshing"></i>

                        <span class="pf-c-spinner pf-m-md" role="progressbar" aria-valuetext="Loading..." style="--pf-c-spinner--Color: #6a6e72" v-if="isRefreshing"
                            ><span class="pf-c-spinner__clipper"></span><span class="pf-c-spinner__lead-ball"></span><span class="pf-c-spinner__tail-ball"></span
                        ></span>
                    </span>
                </button>
            </div>
            <div class="pf-c-card__title">
                <h2 class="pf-c-title pf-m-lg">Configuration Status</h2>
            </div>
        </div>

        <div class="pf-c-card__body">
            <div class="pf-l-grid pf-m-all-6-col-on-sm pf-m-all-3-col-on-lg pf-m-gutter">
                <div class="pf-l-grid__item">
                    <div class="pf-l-flex pf-m-space-items-sm">
                        <div class="pf-l-flex__item">
                            <i class="fas fa-check-circle pf-u-success-color-100" aria-hidden="true"></i>
                        </div>
                        <div class="pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1">
                            <div class="pf-l-flex__item">
                                <router-link to="/devices/status/1">Device Count</router-link>
                            </div>
                            <div class="pf-l-flex__item">
                                <div class="pf-c-skeleton" v-if="isLoading"></div>
                                <span v-if="!isLoading">{{ configinfos['deviceCount'] }} total</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pf-l-grid__item">
                    <div class="pf-l-flex pf-m-space-items-sm">
                        <div class="pf-l-flex__item">
                            <i class="fas fa-exclamation-circle pf-u-danger-color-100" aria-hidden="true"></i>
                        </div>
                        <div class="pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1">
                            <div class="pf-l-flex__item">
                                <router-link to="/devices/status/0">Devices Down</router-link>
                            </div>
                            <div class="pf-l-flex__item">
                                <div class="pf-c-skeleton" v-if="isLoading"></div>
                                <span v-if="!isLoading">{{ configinfos['deviceDownCount'] }} total</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pf-l-grid__item">
                    <div class="pf-l-flex pf-m-space-items-sm">
                        <div class="pf-l-flex__item">
                            <i class="fas fa-check-circle pf-u-success-color-100" aria-hidden="true"></i>
                        </div>
                        <div class="pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1">
                            <div class="pf-l-flex__item">
                                <router-link to="/device/view/configs/0?id=0&status=all">Config Files</router-link>
                            </div>
                            <div class="pf-l-flex__item pf-m-flex-1">
                                <div class="pf-c-skeleton" v-if="isLoading"></div>
                                <span v-if="!isLoading">{{ configinfos['configTotalCount'] }} total</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pf-l-grid__item">
                    <div class="pf-l-flex pf-m-space-items-sm">
                        <div class="pf-l-flex__item">
                            <i class="fas fa-exclamation-circle pf-u-danger-color-100" aria-hidden="true"></i>
                        </div>
                        <div class="pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1">
                            <div class="pf-l-flex__item">
                                <router-link to="/device/view/configs/0?id=0&status=0">Failed Configs</router-link>
                            </div>
                            <div class="pf-l-flex__item pf-m-flex-1">
                                <div class="pf-c-skeleton" v-if="isLoading"></div>
                                <span v-if="!isLoading">{{ configinfos['configDownCount'] }} total</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';

export default {
    props: {},

    setup(props) {
        const configinfos = reactive({});
        const isRefreshing = ref(false);
        const isLoading = ref(false);

        onMounted(() => {
            getConfiginfo();
        });

        function refreshData() {
            console.log('refreshing config details...');
            isRefreshing.value = true;
            getConfiginfo();
        }

        function getConfiginfo() {
            isLoading.value = true;
            axios.get('/api/dashboard/configinfo').then((response) => {
                Object.assign(configinfos, response.data.data);
                isRefreshing.value = false;
                isLoading.value = false;
            });
        }

        return { configinfos, refreshData, isRefreshing, isLoading };
    }
};
</script>
