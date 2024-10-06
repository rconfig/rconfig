<template>
    <div class="pf-c-card">
        <div class="pf-c-card__title">
            <h2 class="pf-c-title pf-m-lg">Config Details</h2>
        </div>
        <div class="pf-c-card__body">
            <dl
                class="pf-c-description-list pf-m-horizontal pf-m-vertical-on-md pf-m-horizontal-on-lg pf-m-vertical-on-xl pf-m-horizontal-on-2xl">
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Status</span>
                    </dt>
                    <device-view-device-details-descr :html="statusText"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Config ID</span>
                    </dt>
                    <device-view-device-details-descr :text="configModel.id"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Device ID</span>
                    </dt>
                    <device-view-device-details-descr :text="configModel.device_id"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Device Name</span>
                    </dt>
                    <device-view-device-details-descr :text="configModel.device_name"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Category</span>
                    </dt>
                    <device-view-device-details-descr
                        :text="configModel.device_category"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Command</span>
                    </dt>
                    <device-view-device-details-descr :text="configModel.command"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Filename</span>
                    </dt>
                    <device-view-device-details-descr
                        :text="configModel.config_filename"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Filesize</span>
                    </dt>
                    <device-view-device-details-descr
                        :text="bytesToSize(configModel.config_filesize)"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Download Duration</span>
                    </dt>
                    <device-view-device-details-descr
                        :text="configModel.duration + ' second'"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Downloaded At</span>
                    </dt>
                    <device-view-device-details-descr
                        :text="formatTime(configModel.created_at)"></device-view-device-details-descr>
                </div>
            </dl>
        </div>
    </div>
</template>

<script>
import { ref, reactive, inject, onMounted } from 'vue';
import DeviceViewDeviceDetailsDescr from './DeviceViewDeviceDetailsDescr.vue';
import { tryOnBeforeUnmount } from '@vueuse/shared';

export default {
    props: {
        configModel: {
            required: true
        }
    },
    components: {
        DeviceViewDeviceDetailsDescr
    },

    setup(props) {
        const statusText = ref('Downloaded');
        const formatTime = inject('formatTime');

        onMounted(() => {
            // <i :class="latestConfig.download_status == '1' ? 'fa fa-check-circle pf-u-success-color-100 ' : 'fa fa-exclamation-triangle pf-u-warning-color-100'"></i>
            if (props.configModel.download_status == '1') {
                statusText.value = '<i class="fa fa-check-circle pf-u-success-color-100"></i><span class="pf-u-color-400">&nbsp; Good config</span>';
            } else if (props.configModel.download_status == '0') {
                statusText.value = '<i class="fa fa-exclamation-triangle pf-u-warning-color-100"></i><span class="pf-u-color-400">&nbsp; Config status unknown</span>';
            } else if (props.configModel.download_status == '2') {
                statusText.value = '<i class="fa fa-exclamation-triangle pf-u-warning-color-100"></i><span class="pf-u-color-400">&nbsp; Config status unknown</span>';
            }
        });
        function bytesToSize(bytes) {
            var sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
            if (bytes == 0) return '0 Byte';
            var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
            return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        }

        return {
            statusText,
            bytesToSize,
            formatTime
        };
    }
};
</script>
