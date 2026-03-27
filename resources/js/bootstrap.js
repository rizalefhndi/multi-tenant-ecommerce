import axios from 'axios';
globalThis.axios = axios;

globalThis.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
globalThis.axios.defaults.withCredentials = true;
globalThis.axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
globalThis.axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
	globalThis.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}
