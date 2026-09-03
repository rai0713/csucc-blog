<script setup>
import { reactive, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';

import { useAuth } from '../state/auth';

const auth = useAuth();
const router = useRouter();
const route = useRoute();

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const loading = ref(false);
const error = ref('');

function extractMessage(exception) {
    return (
        exception.response?.data?.message ??
        Object.values(exception.response?.data?.errors ?? {})
            .flat()
            .shift() ??
        'Unable to create an account.'
    );
}

async function submitForm() {
    loading.value = true;
    error.value = '';

    try {
        await auth.register(form);

        await router.push(route.query.redirect ? String(route.query.redirect) : { name: 'dashboard' });
    } catch (exception) {
        error.value = extractMessage(exception);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <section class="mx-auto max-w-xl rounded-2xl border border-black/10 bg-white p-6 shadow-[0_18px_40px_rgba(15,23,42,0.08)] lg:p-10">
        <div class="h-1 w-16 bg-slate-900"></div>
        <p class="mt-6 text-xs uppercase tracking-[0.35em] text-slate-500">Register</p>
        <h2 class="mt-3 text-3xl font-semibold text-slate-900">Create account</h2>

        <form class="mt-8 space-y-4" @submit.prevent="submitForm">
            <label class="block space-y-2">
                <span class="text-sm text-slate-700">Name</span>
                <input v-model="form.name" class="w-full rounded-md border border-black/10 bg-[#fbfaf7] px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white" type="text" required />
            </label>

            <label class="block space-y-2">
                <span class="text-sm text-slate-700">Email</span>
                <input v-model="form.email" class="w-full rounded-md border border-black/10 bg-[#fbfaf7] px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white" type="email" required />
            </label>

            <label class="block space-y-2">
                <span class="text-sm text-slate-700">Password</span>
                <input v-model="form.password" class="w-full rounded-md border border-black/10 bg-[#fbfaf7] px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white" type="password" required />
            </label>

            <label class="block space-y-2">
                <span class="text-sm text-slate-700">Confirm password</span>
                <input v-model="form.password_confirmation" class="w-full rounded-md border border-black/10 bg-[#fbfaf7] px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:bg-white" type="password" required />
            </label>

            <p v-if="error" class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ error }}
            </p>

            <button class="w-full rounded-md bg-slate-900 px-4 py-3 font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60" type="submit" :disabled="loading">
                {{ loading ? 'Creating account...' : 'Create account' }}
            </button>
        </form>

        <p class="mt-6 text-sm text-slate-600">
            Already registered?
            <RouterLink class="font-semibold text-slate-900 underline decoration-black/20 underline-offset-4 transition hover:decoration-black" :to="{ name: 'login' }">
                Sign in
            </RouterLink>
        </p>
    </section>
</template>