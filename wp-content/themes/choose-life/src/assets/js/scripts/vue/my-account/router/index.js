import { createRouter, createWebHashHistory } from 'vue-router';

import api from '../../api';
import {userStore} from '../../stores/user';
import {uiStore} from '../../stores/ui';

const Account = () => import(/* webpackChunkName: "chunk-account" */'../components/pages/Account.vue');
const Donations = () => import(/* webpackChunkName: "chunk-donations" */'../components/pages/Donations.vue');
const Login = () => import(/* webpackChunkName: "chunk-login" */'../components/pages/Login.vue');
const Register = () => import(/* webpackChunkName: "chunk-register" */'../components/pages/Register.vue');
const ResetPassword = () => import(/* webpackChunkName: "chunk-reset-password" */'../../shared/auth/ResetPassword.vue');

const routes = [
    { 
        path: '/my-account',
        name: 'my-account',
        component: Account,
        meta: { requiresAuth: true },
    },
    { 
        path: '/donations',
        name: 'donations',
        component: Donations,
        meta: { requiresAuth: true },
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { requiresAuth: false },
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { requiresAuth: false },
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: ResetPassword,
        meta: { requiresAuth: false },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/my-account'
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
    const canAccess = await user.validateUser().then(res => {
        return res.success
    });
    if (to.meta.requiresAuth && !canAccess) {
        return next({ name: 'login' });
    }
    switch(to.name) {
        case 'login':
        case 'register':
            if (canAccess) {
                return next({ name: 'my-account' });
            }
    }
    return next();
});

router.afterEach((to, from, failure) => {
    const ui = uiStore();
    if (to.name === 'my-account' && ui.isLoading){
        setTimeout(() => {
            ui.toggleLoading(false);
        }, 450);
    }
});

export default router;
