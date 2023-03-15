import { ref, reactive, inject } from 'vue';
import axios from 'axios';

// https://laraveldaily.com/laravel-8-vue-3-crud-composition-api/
// https://dev.to/razi91/writing-a-composable-function-to-fetch-data-from-rest-api-in-vue-js-4957?utm_source=dormosheio&utm_campaign=dormosheio
export default function useGetAllModeResults(url) {
    const model = reactive({});
    const results = reactive({});
    const isLoading = ref(true);
    const modelUrl = ref(url);
    const createNotification = inject('create-notification');

    const errors = ref('');

    let currentPage = 1;
    let perPage = 10000;
    let filter = '';
    let sortCol = '';
    let sortOrd = '';

    const params = '?page=' + currentPage + '&perPage=' + perPage + '&filter=' + filter + '&sortCol=' + sortCol + '&sortOrd=' + sortOrd;

    axios
        .get('/api/' + modelUrl.value + '/' + params, {})
        .then((response) => {
            // handle success
            Object.assign(results, response.data.data); // just return the data - no pagination
            isLoading.value = false;
        })
        .catch((error) => {
            // handle error
            createNotification({
                type: 'danger',
                title: 'Error',
                message: error.response.data.message
            });
        });

    return {
        results,
        isLoading
    };
}
