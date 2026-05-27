import { createApp } from 'vue';
import ToastPlugin from 'vue-toast-notification';
import { createPinia } from 'pinia';
import App from './App.vue';

import router from './router';

(($) => {
    if ($('#my-account-page').length) {

        const pinia = createPinia();
        const app = createApp(App);

        app.use(ToastPlugin);
        app.use(pinia);
        app.use(router);

        app.mount('#my-account-page');
    }
})(jQuery);
