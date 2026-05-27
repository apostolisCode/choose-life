import { createApp } from 'vue';
import ToastPlugin from 'vue-toast-notification';
import { createPinia } from 'pinia';
import App from './App.vue';
// import { helpers } from '../helpers';

import router from './router';

(($) => {
    if ($('#checkout-page').length) {

        const pinia = createPinia();
        const app = createApp(App);

        app.use(ToastPlugin);
        app.use(pinia);
        app.use(router);

        app.mount('#checkout-page');
    }
})(jQuery);
