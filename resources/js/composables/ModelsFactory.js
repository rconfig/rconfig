import { ref, reactive, inject } from 'vue';
import axios from 'axios';

// https://laraveldaily.com/laravel-8-vue-3-crud-composition-api/
export default function useModels(url, modelObject) {
    const model = reactive({});
    const models = reactive({});
    const isLoading = ref(true);
    const modelUrl = ref(url);
    const createNotification = inject('create-notification');

    const errors = ref('');

    const getModels = async (page, per_page, filterStr, sortColumn, sortOrder) => {
        let currentPage = page ? page : 1;
        let perPage = per_page ? per_page : 10;
        let filter = filterStr ? filterStr : '';
        let sortCol = sortColumn ? sortColumn : '';
        let sortOrd = sortOrder ? sortOrder : '';

        const params = '?page=' + currentPage + '&perPage=' + perPage + '&filter=' + filter + '&sortCol=' + sortCol + '&sortOrd=' + sortOrd;

        axios
            .get('/api/' + modelUrl.value + '/' + params, {})
            .then((response) => {
                // handle success
                Object.assign(models, response.data);
                isLoading.value = false;
            })
            .catch((error) => {
                errors.status = error.response.status;
                errors.message = error.response.data.message;
                createNotification({
                    type: 'danger',
                    title: 'Error',
                    message: error.response.data.message
                });
            });
    };

    const clearModel = async () => {
        Object.assign(model, modelObject);
    };

    const getModel = async (id) => {
        if (id === 0) {
            clearModel();
            isLoading.value = false;
            return;
        }

        errors.value = '';
        try {
            let response = await axios.get(`/api/${modelUrl.value + '/' + id}`);
            Object.assign(model, response.data);
            if (url === 'devices') {
                model.device_vendor = model.vendor[0].id;
            }
            isLoading.value = false;
        } catch (error) {
            if (error.response.status === 422) {
                errors.value = error.response.data.errors;
            } else {
                isLoading.value = false;
                errors.status = error.response.status;
                errors.message = error.response.data.message;
                createNotification({
                    type: 'danger',
                    title: 'Error: ' + error.response.status,
                    message: error.response.data.message
                });
            }
        }
    };

    const getModelClone = async (id) => {
        if (id === 0) {
            clearModel();
            isLoading.value = false;
            return;
        }

        errors.value = '';
        try {
            let response = await axios.get(`/api/${modelUrl.value + '/' + id}`);
            Object.assign(model, response.data);
            if (url === 'devices') {
                model.device_name = response.data.device_name + '-clone';
                model.device_ip = response.data.device_ip + '-clone';
            }
            isLoading.value = false;
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors;
            } else {
                isLoading.value = false;
                errors.status = e.response.status;
                errors.message = e.response.data.message;
                createNotification({
                    type: 'danger',
                    title: 'Error',
                    message: e.response.data.message
                });
            }
        }
    };

    const storeModel = async (data) => {
        errors.value = '';
        try {
            await axios.post('/api/' + modelUrl.value, data);
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors;
            } else {
                errors.status = e.response.status;
                errors.message = e.response.data.message;
                createNotification({
                    type: 'danger',
                    title: 'Error',
                    message: e.response.data.message
                });
            }
        }
    };

    const updateModel = async (data) => {
        errors.value = '';
        try {
            await axios.patch(`/api/${modelUrl.value + '/' + data.id}`, data);
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors;
            } else {
                createNotification({
                    type: 'danger',
                    title: 'Error',
                    message: e.response.data.message
                });
            }
        }
    };

    const destroyModel = async (id, pagenamesingle) => {
        await axios
            .delete(`/api/${modelUrl.value + '/' + id}`)
            .then((response) => {
                createNotification({
                    type: 'success',
                    message: pagenamesingle + ' deleted successfully',
                    duration: 3
                });
            })
            .catch((error) => {
                errors.status = error.response.status;
                errors.message = error.response.data.message;
                createNotification({
                    type: 'danger',
                    title: 'Error',
                    message: error.response.data.message
                });
            });
    };

    return {
        errors,
        model,
        models,
        clearModel,
        getModel,
        getModelClone,
        getModels,
        storeModel,
        updateModel,
        destroyModel,
        isLoading
    };
}
