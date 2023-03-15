/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add a 401 response interceptor for session timeout - https://gist.github.com/yajra/5f5551649b20c8f668aec48549ef5c1f
// window.axios.interceptors.response.use(
//     function(response) {
//         return response;
//     },
//     function(error) {
//         console.log(error.response.status);
//         if (error.response.status === 401) {
//             alert("Your session has expired. You will be redirected to login page.");

//             window.location = "/login";
//         } else {
//             return Promise.reject(error);
//         }
//     }
// );
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        // Show the user a 500 error
        if (error.response.status >= 500) {
            console.error({ 500: error });
        }

        // Handle Session Timeouts
        if (error.response.status === 401) {
            console.error({ 401: error });
            // alert("Your session has expired. You will be redirected to login page.");
            window.location = '/logged-out';
        }

        // Handle Forbidden
        if (error.response.status === 403) {
            console.error({ 403: error });
            // window.location = "/login";
        }

        return Promise.reject(error);
    }
);
