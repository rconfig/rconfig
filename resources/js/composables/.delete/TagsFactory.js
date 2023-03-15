import { ref, reactive } from 'vue'
import axios from 'axios'


// https://laraveldaily.com/laravel-8-vue-3-crud-composition-api/
export default function useTags() {
    const tag = reactive({});
    const tags = reactive({});
    const isLoading = ref(true);

    const errors = ref('')

    const getTags = async (page, per_page, filterStr, sortColumn, sortOrder) => {

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
        let url = '/api/tags' + params;

        axios
        .get(url, {})
        .then((response) => {
            // handle success
            Object.assign(tags, response.data);
            isLoading.value = false;
        })
        .catch((error) => {
            // handle error
            console.log(error);
        });

    }

    const clearTag = async () => {
        Object.assign(tag, {tagname: '', description: ''});
    }

    const getTag = async (id) => {
        if(id === 0 ) {
            clearTag();
            return;
        }

        errors.value = ''
        try {
            let response = await axios.get(`/api/tags/${id}`);
            Object.assign(tag, response.data);
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }

    }

    const storeTag = async (data) => {
        errors.value = ''
        try {
            await axios.post('/api/tags', data)

        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const updateTag = async (data) => {
        errors.value = ''
        try {
            await axios.patch(`/api/tags/${data.id}`, data)
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const destroyTag = async (id) => {
        await axios
        .delete(`/api/tags/${id}` )
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
        tag,
        tags,
        clearTag,
        getTag,
        getTags,
        storeTag,
        updateTag,
        destroyTag,
        isLoading
    }
}