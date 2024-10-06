<template>
    <dd class="pf-c-description-list__description">
        <div class="pf-c-description-list__text copyLinkDD pf-u-text-wrap">
            {{ text }}
            <span v-if="html" v-html="html"></span>
            <button class="pf-c-button pf-m-inline pf-m-link pf-u-color-100 copyLink" type="button" alt="copy" title="copy" @click="copy(text)">
                <copy-icon> </copy-icon>
            </button>
        </div>
    </dd>
</template>

<script>
import { inject } from 'vue';
import CopyIcon from '../../../Icons/CopyLogo.vue';
import useClipboard from 'vue-clipboard3';

export default {
    props: {
        text: {
            type: [String, Number]
        },
        html: {
            type: String
        }
    },
    components: {
        CopyIcon
    },
    setup(props) {
        const createNotification = inject('create-notification');
        const { toClipboard } = useClipboard();

        function copy(value) {
            try {
                toClipboard(value);
                createNotification({
                    type: 'success',
                    message: 'Copied to clipboard!',
                    duration: 3
                });
            } catch (error) {
                createNotification({
                    type: 'danger',
                    title: 'Error',
                    message: error.response
                });
            }
        }
        return {
            CopyIcon,
            copy
        };
    }
};
</script>
