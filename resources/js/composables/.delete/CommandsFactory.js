import { ref, reactive } from 'vue'
import axios from 'axios'


// https://laraveldaily.com/laravel-8-vue-3-crud-composition-api/
export default function useCommands() {
    const command = reactive({});
    const commands = reactive({});
    const isLoading = ref(true);

    const errors = ref('')

    const getCommands = async (page, per_page, filterStr, sortColumn, sortOrder) => {

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
        let url = '/api/commands' + params;

        axios
        .get(url, {})
        .then((response) => {
            // handle success
            Object.assign(commands, response.data);
            isLoading.value = false;
        })
        .catch((error) => {
            // handle error
            console.log(error);
        });

    }

    const clearCommand = async () => {
        Object.assign(command, {command: '', description: ''});
    }

    const getCommand = async (id) => {
        if(id === 0 ) {
            clearCommand();
            return;
        }

        errors.value = ''
        try {
            let response = await axios.get(`/api/commands/${id}`);
            Object.assign(command, response.data);
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }

    }

    const storeCommand = async (data) => {
        errors.value = ''
        try {
            await axios.post('/api/commands', data)

        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const updateCommand = async (data) => {
        errors.value = ''
        try {
            await axios.patch(`/api/commands/${data.id}`, data)
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const destroyCommand = async (id) => {
        await axios
        .delete(`/api/commands/${id}` )
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
        command,
        commands,
        clearCommand,
        getCommand,
        getCommands,
        storeCommand,
        updateCommand,
        destroyCommand,
        isLoading
    }
}