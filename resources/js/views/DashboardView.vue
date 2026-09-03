<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';

import api from '../services/api';
import { useAuth } from '../state/auth';

const auth = useAuth();
const posts = ref([]);
const loading = ref(true);
const error = ref('');

const isAdmin = computed(() => auth.state.user?.role === 'admin');

function formatDate(value) {
    if (!value) {
        return 'Draft';
    }

    return new Intl.DateTimeFormat('en', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(value));
}

async function loadPosts() {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.get('/api/posts');
        posts.value = response.data.data ?? [];
    } catch {
        error.value = 'Unable to load your dashboard right now.';
    } finally {
        loading.value = false;
    }
}

async function deletePost(slug) {
    await api.delete(`/api/posts/${slug}`);
    posts.value = posts.value.filter((post) => post.slug !== slug);
}

onMounted(loadPosts);
</script>

<template>
    <section class="space-y-6">
        <div class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm lg:p-8">
            <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Dashboard</p>
            <h2 class="mt-4 text-3xl font-medium text-slate-900">Welcome, {{ auth.state.user?.name }}</h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 lg:text-base">
                Admins can create, update, and delete posts from here.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <RouterLink
                    v-if="isAdmin"
                    class="inline-flex items-center justify-center rounded-md !bg-slate-950 px-5 py-3 text-sm font-medium !text-white shadow-sm transition hover:!bg-slate-900"
                    :to="{ name: 'post.create' }"
                >
                    Create post
                </RouterLink>
                <RouterLink class="inline-flex items-center justify-center rounded-md border border-black/10 bg-white px-5 py-3 text-sm font-medium text-slate-800 transition hover:bg-slate-50" :to="{ name: 'home' }">
                    View blog
                </RouterLink>
            </div>
        </div>

        <div v-if="loading" class="rounded-2xl border border-black/10 bg-white p-8 text-slate-600">
            Loading dashboard...
        </div>

        <div v-else-if="error" class="rounded-2xl border border-black/10 bg-white p-8 text-slate-700">
            {{ error }}
        </div>

        <div v-else class="grid gap-4 lg:grid-cols-2">
            <article v-for="post in posts" :key="post.slug" class="rounded-2xl border border-black/10 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3 text-xs uppercase tracking-[0.28em] text-slate-500">
                    <span>{{ formatDate(post.published_at) }}</span>
                    <span>{{ post.author?.name ?? 'Editorial desk' }}</span>
                </div>
                <h3 class="mt-4 text-2xl font-medium text-slate-900">
                    {{ post.title }}
                </h3>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    {{ post.excerpt }}
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <RouterLink class="inline-flex items-center justify-center rounded-md border border-black/10 bg-white px-4 py-2 text-sm text-slate-800 transition hover:bg-slate-50" :to="{ name: 'post.show', params: { slug: post.slug } }">
                        Open
                    </RouterLink>
                    <RouterLink
                        v-if="isAdmin"
                        class="inline-flex items-center justify-center rounded-md border border-black/10 bg-white px-4 py-2 text-sm text-slate-800 transition hover:bg-slate-50"
                        :to="{ name: 'post.edit', params: { slug: post.slug } }"
                    >
                        Edit
                    </RouterLink>
                    <button
                        v-if="isAdmin"
                        class="inline-flex items-center justify-center rounded-md border border-black/10 bg-white px-4 py-2 text-sm text-slate-800 transition hover:bg-slate-50"
                        type="button"
                        @click="deletePost(post.slug)"
                    >
                        Delete
                    </button>
                </div>
            </article>
        </div>
    </section>
</template>