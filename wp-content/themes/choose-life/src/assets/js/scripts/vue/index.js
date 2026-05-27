import { createApp } from 'vue';
import ToastPlugin from 'vue-toast-notification';
import { createPinia } from 'pinia';
import App from './App.vue';
import { helpers } from '../helpers';

import router from './router';

helpers.maybeClearStorage();
(($) => {
    if ($('#vue-app').length) {

        const pinia = createPinia();
        const app = createApp(App);

        app.use(ToastPlugin);
        app.use(pinia);
        app.use(router);

        app.mount('#vue-app');
    }
})(jQuery);
