<template>
    <div class="pf-c-card">
        <div class="pf-c-card__title">
            <h2 class="pf-c-title pf-m-xl">Device Details</h2>
        </div>
        <div class="pf-c-card__body">
            <dl class="pf-c-description-list pf-m-horizontal pf-m-vertical-on-md pf-m-horizontal-on-lg pf-m-vertical-on-xl pf-m-horizontal-on-2xl">
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Device ID</span>
                    </dt>
                    <device-view-device-details-descr :text="model.id"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Hostname</span>
                    </dt>
                    <device-view-device-details-descr :text="model.device_name"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">IP Address</span>
                    </dt>
                    <device-view-device-details-descr :text="model.device_ip"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Status</span>
                    </dt>
                    <div class="pf-l-flex pf-m-space-items-sm">
                        <div class="pf-l-flex__item">
                            <i :class="model.status == '1' ? 'fa fa-check-circle pf-u-success-color-100 ' : 'fa fa-exclamation-triangle pf-u-warning-color-100'"></i>
                        </div>
                        <div class="pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1">
                            <div class="pf-l-flex__item">{{ model.status == '1' ? 'Online' : 'Unreachable' }}</div>
                            <div class="pf-l-flex__item">
                                <span class="pf-u-color-400">Last seen: {{ model.last_seen ? formatTimeAgo(model.last_seen) : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="pf-l-grid__item">
                    <div class="pf-l-flex pf-m-space-items-sm">
                        <div class="pf-l-flex__item">
                            <i :class="model.status == '1' ? 'fa fa-check-circle pf-u-success-color-100 ' : 'fa fa-exclamation-triangle pf-u-warning-color-100'"></i>
                        </div>
                        <div class="pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1">
                            <div class="pf-l-flex__item">{{ model.status == '1' ? 'Online' : 'Unreachable' }}</div>
                            <div class="pf-l-flex__item">
                                <span class="pf-u-color-400">Device Status</span>
                            </div>
                        </div>
                    </div>
                </div> -->
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Category</span>
                    </dt>
                    <device-view-device-details-descr v-if="model.category.length != 0" :text="model.category[0].categoryName"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Vendor</span>
                    </dt>
                    <device-view-device-details-descr :text="model.vendor[0].vendorName"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Model</span>
                    </dt>
                    <device-view-device-details-descr :text="model.device_model"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Template</span>
                    </dt>
                    <device-view-device-details-descr v-if="model.template.length != 0" :text="model.template[0].templateName"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Created</span>
                    </dt>
                    <device-view-device-details-descr v-if="model.template.length != 0" :text="model.created_at"></device-view-device-details-descr>
                </div>
                <div class="pf-c-description-list__group">
                    <dt class="pf-c-description-list__term">
                        <span class="pf-c-description-list__text">Tags</span>
                    </dt>
                    <dd class="pf-c-description-list__description">
                        <div class="pf-c-chip" v-for="tag in model.tag" :key="tag.id">
                            <span class="pf-c-chip__text" :alt="tag.tagDescription" :title="tag.tagDescription">{{ tag.tagname }}</span>
                        </div>
                    </dd>
                </div>
            </dl>
        </div>
        <hr class="pf-c-divider" />

        <div class="pf-c-card__footer" style="padding-top: 15px; padding-bottom: 15px; padding-left: 9px">
            <button class="pf-c-button pf-m-link" style="float: right" type="button" @click="openDrawer(model.id)" alt="Edit" title="Edit">Edit Settings</button>
        </div>
    </div>
</template>

<script>
import {} from 'vue';
import DeviceViewDeviceDetailsDescr from './DeviceViewDeviceDetailsDescr.vue';
import { useTimeAgo } from '@vueuse/core';

export default {
    props: {
        model: {
            type: Object,
            default: () => ({})
        }
    },
    emits: ['openDrawer', 'deleteRow', 'pagechanged'],

    components: {
        DeviceViewDeviceDetailsDescr
    },

    setup(props, { emit }) {
        function openDrawer(id, isClone = false) {
            emit('openDrawer', { id: id, isClone: isClone });
        }

        function formatTimeAgo(finished_at) {
            var finished_atNew = finished_at.replace(/AM|PM/g, '');
            var timeAgo = useTimeAgo(new Date(finished_atNew));
            return timeAgo.value;
        }

        return { openDrawer, formatTimeAgo };
    }
};
</script>
