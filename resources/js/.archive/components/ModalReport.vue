<template>
    <div class="pf-c-backdrop">
        <div class="pf-l-bullseye">
            <div class="pf-c-modal-box pf-m-lg" aria-modal="true" ref="clickOutsidetarget">
                <loading-spinner :showSpinner="isLoading"></loading-spinner>

                <button class="pf-c-button pf-m-plain" type="button" aria-label="Close dialog" @click="close" v-if="!isLoading">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
                <!-- <header class="pf-c-modal-box__header" v-if="!isLoading">
                    <h1 class="pf-c-modal-box__title" id="modal-lg-title">Report ID:{{ report_id }}</h1>
                </header> -->
                <div class="pf-c-modal-box__body" v-if="!isLoading">
                    <p id="modal-lg-description"><span v-html="reportHtml"></span></p>
                </div>
                <footer class="pf-c-modal-box__footer" v-if="!isLoading">
                    <!-- <button class="pf-c-button pf-m-primary" type="button">Save</button> -->
                    <button class="pf-c-button pf-m-link" type="button" @click="close()">Close</button>
                </footer>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, inject, onMounted } from 'vue';
import { onClickOutside } from '@vueuse/core';
import LoadingSpinner from './LoadingSpinner.vue';

export default {
    props: {
        report_id: {
            type: String,
            required: true
        }
    },
    emits: ['closeModal'],
    components: {
        LoadingSpinner
    },

    setup(props, { emit }) {
        const createNotification = inject('create-notification');
        const clickOutsidetarget = ref(null);
        const reportHtml = ref('');
        const isLoading = ref(false);

        onClickOutside(clickOutsidetarget, (event) => close());

        onMounted(() => {
            getAndLoadReport();
        });

        function getAndLoadReport() {
            axios
                .get('/api/reports/' + props.report_id)
                .then((response) => {
                    // console.log(response.data);
                    reportHtml.value = response.data;
                    isLoading.value = false;
                })
                .catch((error) => {
                    reportHtml.value = '<h1 class="text-center" style="color: #cc0000;">Error: File not Found!</h1>';
                    isLoading.value = false;

                    createNotification({
                        type: 'danger',
                        title: 'Error',
                        message: error.response
                    });
                });
            // window.open(`/api/reports/${event.row.report_id}/download`);
        }

        function close() {
            emit('closeModal');
        }

        return { isLoading, reportHtml, clickOutsidetarget, close };
    }
};
</script>
