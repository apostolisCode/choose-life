import axios from 'axios';
import {helpers} from '../helpers';

const authRestUrl = `${window.urls.rest}jwt-auth/v1`;
const apiRestUrl = `${window.urls.rest}api/v1`;

function getHeaders() {
    const config = {
        headers: {}
    };
    const sessionData = helpers.getDefaultStoredData('user');
    if (sessionData && sessionData.userData && sessionData.userData.token) {
        config.headers['Authorization'] = `Bearer ${sessionData.userData.token}`;
    }
    return config;
}

const axiosPrivate = axios.create();
axiosPrivate.interceptors.response.use((response) => {
    return response.data;
}, (error) => {
    // Do something with response error
    return error.response.data;
});

const axiosPublic = axios.create({
    headers: {
        common: {}
    }
});
// modify the public instance response schema of axiosPublic
axiosPublic.interceptors.response.use((response) => {
    return response.data;
}, (error) => {
    // Do something with response error
    return error.response.data;
});

export default {
    /*
    * @param email
    * @param password
    */
    async getToken(data) {
        return axiosPublic.post(`${authRestUrl}/token`, data)
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    },
    async validateToken() {
        return axiosPublic.post(`${authRestUrl}/token/validate`, [], getHeaders())
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    },
    async updateUserFields(fields) {
        const data = {
            fields
        };
        return axiosPrivate.post(`${apiRestUrl}/user/update`, data, getHeaders())
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    },
    async registerUser(data) {
        return axiosPublic.post(`${apiRestUrl}/user/register`, data)
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    },
    async placeOrder(data) {
        return axiosPrivate.post(`${apiRestUrl}/place-order`, data, getHeaders())
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    },
    async getDonation(key) {
        const data = {
            id: key
        };
        return axiosPrivate.post(`${apiRestUrl}/donation/get`, data, getHeaders())
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    },
    async payDonation(key, field = 'key') {
        const data = {
            id: key,
            field
        };
        return axiosPrivate.post(`${apiRestUrl}/donation/pay`, data, getHeaders())
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    },
    async getDonations(page) {
        const data = {
            page
        };
        return axiosPrivate.post(`${apiRestUrl}/user/donations`, data, getHeaders())
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    },
    async getSubscriptions(page) {
        const data = {
            page
        };
        return axiosPrivate.post(`${apiRestUrl}/user/subscriptions`, data, getHeaders())
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    },
    async resetPasswordInit(email) {
        const data = {
            email
        };
        return axiosPublic.post(`${apiRestUrl}/user/reset-password/init`, data)
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    },
    async resetPassword(data) {
        return axiosPublic.post(`${apiRestUrl}/user/reset-password`, data)
            .then(response => {
                return response;
            }).catch(error => {
                return error;
            });
    }
};
