import { ref, reactive } from 'vue'
import axios from 'axios'

// https://laraveldaily.com/laravel-8-vue-3-crud-composition-api/
export default function useUsers() {
    const user = reactive({});
    const users = reactive({});
    const isLoading = ref(true);

    const errors = ref('')

    const getUsers = async (page, per_page, filterStr, sortColumn, sortOrder) => {

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
        let url = '/api/users' + params;

        axios
        .get(url, {})
        .then((response) => {
            // handle success
            Object.assign(users, response.data);
            isLoading.value = false;
        })
        .catch((error) => {
            // handle error
            console.log(error);
        });

    }

    const clearUser = async () => {
        Object.assign(user, {name: '', email: '', password: ''});
    }

    const getUser = async (id) => {
        if(id === 0 ) {
            clearUser();
            return;
        }

        errors.value = ''
        try {
            let response = await axios.get(`/api/users/${id}`);
            Object.assign(user, response.data);
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }

    }

    const storeUser = async (data) => {
        errors.value = ''
        try {
            await axios.post('/api/users', data)

        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const updateUser = async (data) => {
        errors.value = ''
        try {
            await axios.patch(`/api/users/${data.id}`, data)
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const destroyUser = async (id) => {
        await axios
        .delete(`/api/users/${id}` )
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
        user,
        users,
        clearUser,
        getUser,
        getUsers,
        storeUser,
        updateUser,
        destroyUser,
        isLoading
    }
}