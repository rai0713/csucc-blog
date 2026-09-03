<script setup>
import { computed, onMounted } from 'vue';
import { useRoute, useRouter, RouterLink, RouterView } from 'vue-router';

import { useAuth } from './state/auth';

const router = useRouter();
const route = useRoute();
const auth = useAuth();

const isAdmin = computed(() => auth.state.user?.role === 'admin');

onMounted(async () => {
    if (!auth.state.ready) {
        await auth.bootstrapAuth();
    }
});

async function handleLogout() {
    await auth.logout();
    await router.push({ name: 'home' });
}
</script>

<template>
    <div class="min-h-screen bg-[#f6f5f1] text-slate-900">
        <header class="sticky top-0 z-20 border-b border-black/5 bg-white/85 backdrop-blur-sm">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-5 py-4 lg:px-8">
                <RouterLink class="flex items-center gap-3" :to="{ name: 'home' }">
                    <img
                        alt="CSUCC Blog logo"
                        class="h-9 w-9 rounded-sm object-contain"
                        :src="'/images/csucc-logo.png'"
                    >
                    <span class="leading-tight">
                      
                        <span class="block text-sm text-slate-900">CSUCC Blog</span>
                    </span>
                </RouterLink>

                <nav class="flex items-center gap-2 text-sm text-slate-700">
                    <RouterLink class="inline-flex items-center justify-center rounded-md px-3 py-2 transition hover:bg-black/5" :class="route.name === 'home' ? 'bg-black/5 text-slate-950' : ''" :to="{ name: 'home' }">
                        Home
                    </RouterLink>

                    <RouterLink v-if="auth.state.token" class="inline-flex items-center justify-center rounded-md px-3 py-2 transition hover:bg-black/5" :class="route.name === 'dashboard' ? 'bg-black/5 text-slate-950' : ''" :to="{ name: 'dashboard' }">
                        Dashboard
                    </RouterLink>

                    <RouterLink v-if="!auth.state.token" class="inline-flex items-center justify-center rounded-md px-3 py-2 transition hover:bg-black/5" :to="{ name: 'login' }">
                        Login
                    </RouterLink>

                    <RouterLink v-if="!auth.state.token" class="inline-flex items-center justify-center rounded-md !bg-slate-950 px-4 py-2 font-semibold !text-white shadow-sm transition hover:!bg-slate-900" :to="{ name: 'register' }">
                        Sign up
                    </RouterLink>

                    <button v-if="auth.state.token" class="inline-flex items-center justify-center rounded-md !bg-slate-950 px-4 py-2 font-semibold !text-white shadow-sm transition hover:!bg-slate-900" type="button" @click="handleLogout">
                        Logout
                    </button>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl px-5 py-8 lg:px-8 lg:py-10">
            <RouterView v-slot="{ Component }">
                <transition name="fade" mode="out-in">
                    <component :is="Component" />
                </transition>
            </RouterView>
        </main>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(4px);
}
</style>