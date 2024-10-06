<template>
    <system-banner :settings="allSettings"></system-banner>

    <system-time-zone :settings="allSettings"></system-time-zone>

    <system-email :settings="allSettings"> </system-email>
</template>

<script>
import { onMounted, reactive } from 'vue';

import SystemBanner from './components/SystemBanner.vue';
import SystemTimeZone from './components/SystemTimeZone.vue';
import SystemEmail from './components/SystemEmail.vue';

export default {
    props: {},

    components: {
        SystemBanner,
        SystemTimeZone,
        SystemEmail
    },

    setup(props) {
        const allSettings = reactive({});

        onMounted(() => {
            getAllSettings();
        });

        function getAllSettings() {
            axios
                .get('/api/settings/settings/1', {})
                .then((response) => {
                    Object.assign(allSettings, response.data);
                })
                .catch((error) => {
                    // handle error
                    console.log(error);
                });
        }
        return {
            SystemBanner,
            SystemTimeZone,
            SystemEmail,
            allSettings
        };
    }
};
</script>
