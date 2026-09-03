import { createRouter, createWebHistory } from 'vue-router';

import { useAuth } from '../state/auth';
import BlogHomeView from '../views/BlogHomeView.vue';
import DashboardView from '../views/DashboardView.vue';
import LoginView from '../views/LoginView.vue';
import PostDetailView from '../views/PostDetailView.vue';
import PostEditorView from '../views/PostEditorView.vue';
import RegisterView from '../views/RegisterView.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: BlogHomeView,
    },
    {
        path: '/posts/:slug',
        name: 'post.show',
        component: PostDetailView,
        props: true,
    },
    {
        path: '/login',
        name: 'login',
        component: LoginView,
    },
    {
        path: '/register',
        name: 'register',
        component: RegisterView,
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: DashboardView,
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/dashboard/posts/new',
        name: 'post.create',
        component: PostEditorView,
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/dashboard/posts/:slug/edit',
        name: 'post.edit',
        component: PostEditorView,
        props: true,
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return {
            top: 0,
        };
    },
});

const auth = useAuth();

router.beforeEach(async (to) => {
    if (!auth.state.ready) {
        await auth.bootstrapAuth();
    }

    if (to.meta.requiresAuth && !auth.state.token) {
        return {
            name: 'login',
            query: {
                redirect: to.fullPath,
            },
        };
    }

    if (to.meta.requiresAdmin && auth.state.user?.role !== 'admin') {
        return {
            name: 'dashboard',
        };
    }

    if ((to.name === 'login' || to.name === 'register') && auth.state.token) {
        return {
            name: 'dashboard',
        };
    }

    return true;
});

export default router;