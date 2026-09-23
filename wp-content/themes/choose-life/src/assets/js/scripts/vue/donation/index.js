import { createApp } from 'vue';
import { createPinia } from 'pinia';
import DonationPicker from './DonationPicker.vue';

// Donation page (templates/donation.php): only the amounts picker is Vue, the
// same component as the checkout's "Donation" step
(($) => {
    if ($('#donation-amounts-app').length && window.donation_page) {
        const app = createApp(DonationPicker);
        app.use(createPinia());
        app.mount('#donation-amounts-app');
    }
})(jQuery);
