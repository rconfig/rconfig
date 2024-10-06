<template>
    <div v-cloak v-if="!isLoading">
        <div class="pf-c-alert pf-m-warning pf-m-inline" aria-label="Inline warning alert" v-if="warningStatus === 0">
            <div class="pf-c-alert__icon">
                <i class="fas fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
            </div>
            <p class="pf-c-alert__title">
                <span class="pf-screen-reader">Warning:</span>
                You have not removed the default admin user
            </p>
            <div class="pf-c-alert__action">
                <button @click="hideAdminWarningBanner()" class="pf-c-button pf-m-plain" type="button" aria-label="Close success alert: Success alert title">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';

export default {
    props: {},

    setup(props) {
        const warningStatus = ref(0);
        const isLoading = ref(true);

        onMounted(() => {
            if (localStorage.getItem('hideAdminWarningBanner')) {
                warningStatus.value = 1;
                isLoading.value = false;
                return;
            }
            getDefaultAdmin();
        });

        function getDefaultAdmin() {
            axios.get('/api/users?page=1&perPage=10&filter=admin@domain.com&sortCol=&sortOrd=desc').then((response) => {
                // console.log(response.data.data.length);
                if (response.data.data.length > 0) {
                    warningStatus.value = 0;
                } else {
                    warningStatus.value = 1;
                }
                isLoading.value = false;
            });
        }

        function hideAdminWarningBanner() {
            // when clicked set local storage to hide the banner
            localStorage.setItem('hideAdminWarningBanner', true);
            warningStatus.value = 1;
        }

        return {
            isLoading,
            warningStatus,
            hideAdminWarningBanner
        };
    }
};
</script>
