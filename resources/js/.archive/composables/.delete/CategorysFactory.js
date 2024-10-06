import { ref, reactive } from 'vue'
import axios from 'axios'


// https://laraveldaily.com/laravel-8-vue-3-crud-composition-api/
export default function useCategorys() {
    const category = reactive({});
    const categorys = reactive({});
    const isLoading = ref(true);

    const errors = ref('')

    const getCategorys = async (page, per_page, filterStr, sortColumn, sortOrder) => {

        let currentPage = page ? page : 1;
        let perPage = per_page ? per_page : 10;
        let filter = filterStr ? filterStr : '';
        let sortCol = sortColumn ? sortColumn : '';
        let sortOrd = sortOrder ? sortOrder : '';

        const params =
        "?page=" +
        currentPage +
        "&perPage=" +
        perPage +
        "&filter=" +
        filter +
        "&sortCol=" +
        sortCol +
        "&sortOrd=" +
        sortOrd; 
        let url = '/api/categories' + params;

        axios
        .get(url, {})
        .then((response) => {
            // handle success
            Object.assign(categorys, response.data);
            isLoading.value = false;
        })
        .catch((error) => {
            // handle error
            console.log(error);
        });

    }

    const clearCategory = async () => {
        Object.assign(category, {categoryName: '', categoryDescription: ''});
    }

    const getCategory = async (id) => {
        if(id === 0 ) {
            clearCategory();
            return;
        }

        errors.value = ''
        try {
            let response = await axios.get(`/api/categories/${id}`);
            Object.assign(category, response.data);
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }

    }

    const storeCategory = async (data) => {
        errors.value = ''
        try {
            await axios.post('/api/categories', data)

        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const updateCategory = async (data) => {
        errors.value = ''
        try {
            await axios.patch(`/api/categories/${data.id}`, data)
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const destroyCategory = async (id) => {
        await axios
        .delete(`/api/categories/${id}` )
        .then((response) => {
            // handle success
            console.log(response);
        })
        .catch((error) => {
            // handle error
            console.log(error);
        });
    }

    return {
        errors,
        category,
        categorys,
        clearCategory,
        getCategory,
        getCategorys,
        storeCategory,
        updateCategory,
        destroyCategory,
        isLoading
    }
}