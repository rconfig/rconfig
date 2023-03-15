<template>
    <div class="pf-c-backdrop">
        <div class="pf-l-bullseye">
            <div class="pf-c-about-modal-box" role="dialog" ref="clickOutsidetarget" style="--pf-c-about-modal-box--Height: calc(100% - (var(--pf-global--spacer--4xl) * 2))">
                <div class="pf-c-about-modal-box__brand">
                    <img src="/images/artwork_white_horizontalArtboard 1_72px_TM.png" alt="rConfig brand logo" style="width: auto" />
                </div>
                <div class="pf-c-about-modal-box__close">
                    <button class="pf-c-button pf-m-plain" type="button" aria-label="Close dialog" @click="close">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="pf-c-about-modal-box__header">
                    <h1 class="pf-c-title pf-m-4xl" id="about-modal-title">rConfig - Network Configuration Management</h1>
                </div>
                <div class="pf-c-about-modal-box__hero"></div>
                <div class="pf-c-about-modal-box__content">
                    <div class="pf-c-content">
                        <dl>
                            <dt>App Version</dt>
                            <dd>{{ licenseInfo.version }}</dd>
                            <dt>License ID</dt>
                            <dd>{{ licenseInfo.sub_id }}</dd>
                            <dt>Licensee Name</dt>
                            <dd>{{ licenseInfo.sub_name }}</dd>
                            <dt>License Status</dt>
                            <dd>{{ licenseInfo.status }}</dd>
                            <dt>License Expiry</dt>
                            <dd>{{ licenseInfo.expiry }}</dd>
                        </dl>
                        <button class="pf-c-button pf-m-link pf-u-pl-xs" type="button" @click="copy(licenseInfo)">
                            <span class="pf-c-button__icon pf-m-start">
                                <i class="fas fa-copy" aria-hidden="true"></i>
                            </span>
                            {{ copied }}
                        </button>
                    </div>

                    <p class="pf-c-about-modal-box__strapline">© rConfig {{ new Date().getFullYear() }} all rights reserved. rConfig&trade; is a registered Trademark of rConfig.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import useClipboard from 'vue-clipboard3';
import { onClickOutside } from '@vueuse/core';
import { ref, onMounted, reactive, inject } from 'vue';

export default {
    props: {},

    setup(props, { emit }) {
        const clickOutsidetarget = ref(null);
        const copied = ref('Copy to clipboard');
        const createNotification = inject('create-notification');
        const licenseInfo = reactive({});
        const { toClipboard } = useClipboard();

        onClickOutside(clickOutsidetarget, (event) => close());

        onMounted(() => {
            getLicenseInfo();
        });

        function getLicenseInfo() {
            axios.get('/api/license-info').then((response) => {
                Object.assign(licenseInfo, response.data.data);
            });
        }

        const copy = async (value) => {
            // console.log(value);
            try {
                await toClipboard(JSON.stringify(value));
                copied.value = 'Copied!';
                setTimeout(() => {
                    copied.value = 'Copy to clipboard';
                }, 2000);
                createNotification({
                    type: 'success',
                    title: 'Copy Success',
                    message: 'Output copied to clipboard'
                });
            } catch (e) {
                createNotification({
                    type: 'danger',
                    title: 'Error',
                    message: e
                });
            }
        };

        const close = () => {
            emit('close');
        };

        return {
            copy,
            copied,
            licenseInfo,
            clickOutsidetarget,
            close
        };
    }
};
</script>
