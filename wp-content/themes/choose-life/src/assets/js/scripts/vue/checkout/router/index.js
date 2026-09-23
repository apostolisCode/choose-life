import { createRouter, createWebHashHistory, START_LOCATION } from 'vue-router';

import {userStore} from '../../stores/user';
import {uiStore} from '../../stores/ui';
import {checkoutFlow} from '../flow';

const Complete = () => import(/* webpackChunkName: "chunk-complete" */'../components/pages/Complete.vue');
const Start = () => import(/* webpackChunkName: "chunk-start" */'../components/pages/Start.vue');
const Login = () => import(/* webpackChunkName: "chunk-login" */'../components/pages/Login.vue');
const Register = () => import(/* webpackChunkName: "chunk-register" */'../components/pages/Register.vue');
const Payment = () => import(/* webpackChunkName: "chunk-payment" */'../components/pages/Payment.vue');
const NoDonation = () => import(/* webpackChunkName: "chunk-no-donation" */'../components/pages/NoDonation.vue');

const ResetPassword = () => import(/* webpackChunkName: "chunk-reset-password" */'../../shared/auth/ResetPassword.vue');

const routes = [
    {
        path: '/complete',
        name: 'complete',
        component: Complete,
        meta: { requiresAuth: false },
    },
    {
        path: '/',
        name: 'start',
        component: Start,
        meta: { requiresAuth: false },
        redirect: { name: 'login' },
        children: [
            {
                path: '',
                name: 'login',
                component: Login,
            },
            {
                path: 'register',
                name: 'register',
                component: Register,
            },
            {
                // part of step 1, so it shares the stepper/container of Start
                path: 'reset-password',
                name: 'reset-password',
                component: ResetPassword,
                props: { embedded: true },
            },
        ]
    },
    {
        path: '/payment/:donation_key',
        name: 'payment',
        component: Payment,
        meta: { requiresAuth: false },
    },
    {
        // step 2: choose the donation amount
        path: '/donation',
        alias: '/empty',
        name: 'donation',
        component: NoDonation,
        meta: { requiresAuth: false },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    }
});

router.beforeEach(async (to, from, next) => {
    const user = userStore();
    const donationAmount = user.getDonationAmount;
    const isLoggedIn = await user.validateUser().then(res => {
        return res.success
    });
    if (from === START_LOCATION) {
        checkoutFlow.init(donationAmount, isLoggedIn);
    }
    if (to.name === 'complete' && donationAmount <= 0) {
        return next({ name: 'donation' });
    }
    switch(to.name) {
        case 'login':
        case 'register':
            if (isLoggedIn) {
                return next({ name: 'complete' });
            }
    }
    return next();
});

router.afterEach((to, from, failure) => {
    const ui = uiStore();
    setTimeout(() => {
        ui.toggleLoading(false);
    }, 450);
});

export default router;
