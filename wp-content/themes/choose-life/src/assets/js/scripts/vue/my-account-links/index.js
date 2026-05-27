import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { helpers } from '../helpers';
import MyAccountLinks from './MyAccountLinks.vue';

helpers.maybeClearStorage();
(($) => {
    if ($('#account-menu-links').length &&
        !$('body').hasClass('page-template-my-account')) {

        const pinia = createPinia();
        const app = createApp(MyAccountLinks);

        app.use(pinia);

        app.mount('#account-menu-links');
    }
})(jQuery);
