import { reactive, readonly } from 'vue';

import api from '../services/api';

const storageKey = 'csucc-blog-auth';

function loadStoredSession() {
    try {
        const storedSession = localStorage.getItem(storageKey);

        return storedSession ? JSON.parse(storedSession) : {};
    } catch {
        return {};
    }
}

function persistSession(token, user) {
    localStorage.setItem(storageKey, JSON.stringify({ token, user }));
}

const storedSession = loadStoredSession();

const state = reactive({
    token: storedSession.token ?? '',
    user: storedSession.user ?? null,
    ready: false,
});

function applyToken(token) {
    if (token) {
        api.defaults.headers.common.Authorization = `Bearer ${token}`;
    } else {
        delete api.defaults.headers.common.Authorization;
    }
}

function setSession(data) {
    state.token = data.token;
    state.user = data.user;
    applyToken(data.token);
    persistSession(data.token, data.user);
}

function clearSession() {
    state.token = '';
    state.user = null;
    applyToken('');
    localStorage.removeItem(storageKey);
}

async function bootstrapAuth() {
    if (!state.token) {
        state.ready = true;
        return;
    }

    applyToken(state.token);

    try {
        const response = await api.get('/api/me');
        state.user = response.data.user;
        persistSession(state.token, state.user);
    } catch {
        clearSession();
    } finally {
        state.ready = true;
    }
}

async function register(payload) {
    const response = await api.post('/api/register', payload);
    setSession(response.data);
    state.ready = true;

    return response.data;
}

async function login(payload) {
    const response = await api.post('/api/login', payload);
    setSession(response.data);
    state.ready = true;

    return response.data;
}

async function logout() {
    if (state.token) {
        await api.post('/api/logout');
    }

    clearSession();
    state.ready = true;
}

export function useAuth() {
    return {
        state: readonly(state),
        bootstrapAuth,
        register,
        login,
        logout,
        clearSession,
    };
}