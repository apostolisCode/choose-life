import axios from 'axios';

const ajaxUrl = window.urls.ajax;

// Nonce for admin-ajax requests. Localized fresh on every page load and
// refreshed in-memory after a login/register (the user's session — and thus
// the valid nonce — changes when they authenticate without a page reload).
let currentNonce = window.urls.nonce;

const client = axios.create({
    withCredentials: true,
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    }
});
client.interceptors.response.use((response) => {
    return response.data;
}, (error) => {
    return error.response.data;
});

/**
 * POST an admin-ajax request. The payload is JSON encoded into a single field
 * and decoded server-side, so nested data (e.g. `fields`) survives.
 *
 * @param {string} action  the wp_ajax_ action name
 * @param {object} data    the payload object
 */
function post(action, data = {}) {
    const body = new URLSearchParams({
        action,
        _ajax_nonce: currentNonce,
        payload: JSON.stringify(data)
    });
    return client.post(ajaxUrl, body)
        .then(response => {
            return response;
        }).catch(error => {
            return error;
        });
}

export default {
    async getToken(data) {
        const res = await post('cl_login', data);
        if (res && res.success && res.nonce) {
            currentNonce = res.nonce;
        }
        return res;
    },
    async validateToken() {
        const res = await post('cl_me');
        if (res && res.success && res.nonce) {
            currentNonce = res.nonce;
        }
        return res;
    },
    async registerUser(data) {
        const res = await post('cl_register', data);
        if (res && res.success && res.nonce) {
            currentNonce = res.nonce;
        }
        return res;
    },
    async logout() {
        return post('cl_logout');
    },
    async updateUserFields(fields) {
        return post('cl_update_user', { fields });
    },
    async placeOrder(data) {
        return post('cl_place_order', data);
    },
    async getDonation(key) {
        return post('cl_get_donation', { id: key });
    },
    async payDonation(key, field = 'key') {
        return post('cl_pay_donation', { id: key, field });
    },
    async getDonations(page) {
        return post('cl_user_donations', { page });
    },
    async getSubscriptions(page) {
        return post('cl_user_subscriptions', { page });
    },
    async resetPasswordInit(email) {
        return post('cl_reset_password_init', { email });
    },
    async resetPassword(data) {
        return post('cl_reset_password', data);
    }
};
