import { ref, reactive } from 'vue'
import axios from 'axios'


// https://laraveldaily.com/laravel-8-vue-3-crud-composition-api/
export default function useVendors() {
    const vendor = reactive({});
    const vendors = reactive({});
    const isLoading = ref(true);

    const errors = ref('')

    const getVendors = async (page, per_page, filterStr, sortColumn, sortOrder) => {

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
        let url = '/api/vendors' + params;

        axios
        .get(url, {})
        .then((response) => {
            // handle success
            Object.assign(vendors, response.data);
            isLoading.value = false;
        })
        .catch((error) => {
            // handle error
            console.log(error);
        });

    }

    const clearVendor = async () => {
        Object.assign(vendor, {vendorName: ''});
    }

    const getVendor = async (id) => {
        if(id === 0 ) {
            clearVendor();
            return;
        }

        errors.value = ''
        try {
            let response = await axios.get(`/api/vendors/${id}`);
            Object.assign(vendor, response.data);
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }

    }

    const storeVendor = async (data) => {
        errors.value = ''
        try {
            await axios.post('/api/vendors', data)

        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const updateVendor = async (data) => {
        errors.value = ''
        try {
            await axios.patch(`/api/vendors/${data.id}`, data)
        } catch (e) {
            if (e.response.status === 422) {
                errors.value = e.response.data.errors
            }
        }
    }

    const destroyVendor = async (id) => {
        await axios
        .delete(`/api/vendors/${id}` )
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
        vendor,
        vendors,
        clearVendor,
        getVendor,
        getVendors,
        storeVendor,
        updateVendor,
        destroyVendor,
        isLoading
    }
}