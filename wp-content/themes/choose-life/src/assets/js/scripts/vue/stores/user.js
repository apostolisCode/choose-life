import { defineStore } from 'pinia';
import { helpers } from '../helpers';
import api from '../api';

let defaultData = {};
const storageData = helpers.getDefaultStoredData('user');
if (storageData) {
    defaultData = storageData;
}
export const userStore = defineStore('user', {
    state: () => ({
        ...{
            userData: false,
            donationAmount: 0
        },
        ...storageData
    }),
    getters: {
        isLoggedIn(state) {
            return state.userData !== false;
        },
        getUserData(state) {
            return state.userData;
        },
        getDonationAmount(state) {
            return state.donationAmount;
        }
    },
    actions: {
        setDonationAmount(amount) {
            this.donationAmount = amount;
            helpers.updateStorage('user', {
                donationAmount: amount
            });
        },
        async userRegister(email, password) {
            const postData = {
                email,
                password
            };
            return api.registerUser(postData)
                .then(res => {
                    if (res.success) {
                        this.userData = res.data;
                        helpers.updateStorage('user', {
                            userData: res.data
                        });
                    }
                    return res;
                });
        },
        async userLogin(email, password) {
            const postData = {
                username: email,
                password
            };
            return api.getToken(postData)
                .then(res => {
                    if (res.success) {
                        this.userData = res.data;
                        helpers.updateStorage('user', {
                            userData: res.data
                        });
                    }
                    return res;
                });
        },
        async validateUser() {
            return api.validateToken()
                .then(res => {
                    if (res.success) {
                        this.userData = res.data;
                        helpers.updateStorage('user', {
                            userData: res.data
                        });
                    }
                    return res;
                });
        },
        async updateUser(fields) {
            return api.updateUserFields(fields)
                .then(res => {
                    return res;
                });
        },
        async userLogout() {
            await api.logout();
            helpers.clearStorage('user');
            this.userData = false;
        },
        getDonations(page) {
            return api.getDonations(page)
                .then(res => {
                    return res;
                });
        },
        getSubscriptions(page) {
            return api.getSubscriptions(page)
                .then(res => {
                    return res;
                });
        },
        resetPasswordInit(email) {
            return api.resetPasswordInit(email)
                .then(res => {
                    return res;
                });
        },
        resetPassword(email, otp, password) {
            const data = {
                email,
                otp,
                password
            };
            return api.resetPassword(data)
                .then(res => {
                    return res;
                });
        }
    }
});
