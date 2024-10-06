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
                <h2 class="pf-c-title pf-m-lg">System Details</h2>
            </div>
        </div>
        <div class="pf-c-card__body">
            <dl class="pf-c-description-list">
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">OS VERSION</dt>
                    <div class="pf-c-skeleton" v-if="isLoading"></div>
                    <device-view-device-details-descr v-if="!isLoading" :text="sysinfo.OSVersion"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">SERVERNAME</dt>
                    <div class="pf-c-skeleton" v-if="isLoading"></div>
                    <device-view-device-details-descr v-if="!isLoading" :text="sysinfo.ServerName"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">LOCAL IP</dt>
                    <div class="pf-c-skeleton" v-if="isLoading"></div>
                    <device-view-device-details-descr v-if="!isLoading" :text="sysinfo.localIp"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">PUBLIC IP</dt>
                    <div class="pf-c-skeleton" v-if="isLoading"></div>
                    <device-view-device-details-descr v-if="!isLoading" :text="sysinfo.PublicIP"></device-view-device-details-descr>
                </div>

                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">PHP / LARAVEL VERSION</dt>
                    <div class="pf-c-skeleton" v-if="isLoading"></div>

                    <device-view-device-details-descr v-if="!isLoading" :text="sysinfo.PHPVersion"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">REDIS VERSION</dt>
                    <div class="pf-c-skeleton" v-if="isLoading"></div>

                    <device-view-device-details-descr v-if="!isLoading" :text="sysinfo.RedisVersion"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">MYSQL VERSION</dt>
                    <div class="pf-c-skeleton" v-if="isLoading"></div>

                    <device-view-device-details-descr v-if="!isLoading" :text="sysinfo.MySQLVersion"></device-view-device-details-descr>
                </div>
            </dl>
        </div>
        <hr class="pf-c-divider" />
        <div class="pf-c-card__footer">
            <router-link to="/settings/overview">View Settings</router-link>
        </div>
    </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import DeviceViewDeviceDetailsDescr from '../DevicesViews/components/DeviceViewDeviceDetailsDescr.vue';

export default {
    props: {},
    components: {
        DeviceViewDeviceDetailsDescr
    },

    setup(props) {
        const sysinfo = reactive({});
        const isRefreshing = ref(false);
        const isLoading = ref(false);

        onMounted(() => {
            getSysteminfo();
        });

        function refreshData() {
            console.log('refreshing server details...');
            isRefreshing.value = true;
            getSysteminfo();
        }

        function getSysteminfo() {
            isLoading.value = true;
            axios.get('/api/dashboard/sysinfo').then((response) => {
                Object.assign(sysinfo, response.data);
                isRefreshing.value = false;
                isLoading.value = false;
            });
        }

        return { sysinfo, refreshData, isRefreshing, isLoading };
    }
};
</script>
