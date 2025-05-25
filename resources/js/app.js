import { createApp } from 'vue';
import App from './App.vue';

// Plugins
import axios from 'axios';
import Cookies from 'js-cookie';
import store from './store.js';
import router from './router';
import vuetify from '../plugins/vuetify.js';

// CASL Abilities
import { abilitiesPlugin } from '@casl/vue';
import { ability } from './ability.js';

// Global SCSS (make sure this file exists)
import '../scss/style.scss';

// Create Vue app instance
const app = createApp(App);

// Register plugins
app.use(vuetify);
app.use(store);
app.use(router);
app.use(abilitiesPlugin, ability, {
  useGlobalProperties: true,
});

// Set up Axios globally
app.config.globalProperties.$axios = axios;

const token = Cookies.get('access_token') || localStorage.getItem('access_token');
if (token) {
  axios.defaults.headers.common['Authorization'] = token;
}
axios.defaults.headers.common['Content-Type'] = 'application/json';

// Axios error handling (401 logout)
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      Cookies.remove('access_token');
      localStorage.removeItem('access_token');
      localStorage.removeItem('user_login');
      store.dispatch('login/setPermissions', []);
      router.replace({ name: 'adminLogin' });
    }
    return Promise.reject(error);
  }
);

// Mount app
app.mount('#app');