import { defineStore } from 'pinia';
import { helpers } from '../helpers';
import api from '../api';

let defaultData = {};
const storageData = helpers.getDefaultStoredData('user');
if (storageData) {
    defaultData = storageData;
}

// the one session check of this page load (see validateUser)
let sessionCheck = null;
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
        /**
         * Whether a user is signed in, for the routers' guards. The session is
         * read once per page load — from app_config.user printed with the page,
         * else with cl_me — and then kept by the store: login, register and
         * logout update it, so navigating needs no request.
         */
        async validateUser() {
            if (!sessionCheck) {
                const initial = window.app_config && window.app_config.user;
                sessionCheck = (initial !== undefined
                    ? Promise.resolve(initial ? {success: true, data: initial} : {success: false})
                    : api.validateToken())
                    .then(res => {
                        this.userData = res && res.success ? res.data : false;
                        helpers.updateStorage('user', {
                            userData: this.userData
                        });
                    })
                    .catch(() => {
                        sessionCheck = null;   // try again on the next navigation
                        this.userData = false;
                    });
            }
            await sessionCheck;

            return {success: this.isLoggedIn, data: this.userData};
        },
        async updateUser(fields) {
            return api.updateUserFields(fields)
                .then(res => {
                    if (res.success) {
                        // the pages read the profile from the store
                        this.userData = {...this.userData, ...fields};
                        helpers.updateStorage('user', {
                            userData: this.userData
                        });
                    }
                    return res;
                });
        },
        async changePassword(currentPassword, newPassword) {
            return api.changePassword({
                current_password: currentPassword,
                new_password: newPassword
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
