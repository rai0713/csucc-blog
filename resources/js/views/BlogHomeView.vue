<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';

import api from '../services/api';
import { useAuth } from '../state/auth';

const auth = useAuth();
const posts = ref([]);
const meta = ref(null);
const loading = ref(true);
const error = ref('');
const search = ref('');

const canManage = computed(() => auth.state.user?.role === 'admin');
const filteredPosts = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return posts.value;
    }

    return posts.value.filter((post) => {
        return [post.title, post.excerpt, post.author?.name]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(query));
    });
});

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
        meta.value = response.data.meta ?? null;
    } catch {
        posts.value = [];
        meta.value = null;
    } finally {
        loading.value = false;
    }
}

onMounted(loadPosts);
</script>

<template>
    <div class="space-y-8">
        <section class="mx-auto max-w-4xl pt-8 text-center lg:pt-12">
            <h1 class="mt-4 text-4xl font-medium tracking-tight text-slate-900 lg:text-6xl">
                Welcome to CSUCC Blog
            </h1>



            <div class="mt-6 flex flex-wrap justify-center gap-3 text-sm">
                <RouterLink v-if="canManage" class="rounded-sm border border-black/10 bg-white px-4 py-2 text-slate-800 transition hover:bg-slate-50" :to="{ name: 'dashboard' }">
                    Go to dashboard
                </RouterLink>
                <RouterLink v-else class="rounded-sm border border-black/10 bg-white px-4 py-2 text-slate-800 transition hover:bg-slate-50" :to="{ name: 'login' }">
                    Log in to manage posts
                </RouterLink>
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between text-sm text-slate-500">
                <span>{{ filteredPosts.length }} results</span>
                <span v-if="meta">Published posts</span>
            </div>

            <div v-if="loading" class="rounded-2xl border border-black/10 bg-white p-8 text-slate-600">
                Loading posts...
            </div>

            <div v-else-if="error" class="rounded-2xl border border-black/10 bg-white p-8 text-slate-700">
                {{ error }}
            </div>

            <div v-else-if="filteredPosts.length === 0" class="rounded-sm border border-black/10 bg-white p-8 text-slate-600">
                <p class="text-sm">
                    No posts found right now.
                </p>
                <p class="mt-2 text-sm text-slate-500">
                    If the database is empty, run the seeders. If you searched something, clear the search and try again.
                </p>
            </div>

            <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <article v-for="post in filteredPosts" :key="post.slug" class="overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm transition hover:shadow-md">
                    <div class="h-40 bg-[#e9e6de]">
                        <div class="flex h-full items-end justify-between p-5 text-xs uppercase tracking-[0.24em] text-slate-500">
                            <span>{{ formatDate(post.published_at) }}</span>
                            <span>{{ post.author?.name ?? 'Editorial desk' }}</span>
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="text-lg font-medium text-slate-900">
                            {{ post.title }}
                        </h3>
                        <p class="mt-2 line-clamp-3 text-sm leading-6 text-slate-600">
                            {{ post.excerpt }}
                        </p>
                        <div class="mt-4 flex items-center justify-between gap-3">
                            <RouterLink class="rounded-md border border-black/10 bg-white px-3 py-2 text-sm font-medium text-slate-900 transition hover:bg-slate-50" :to="{ name: 'post.show', params: { slug: post.slug } }">
                                Read more
                            </RouterLink>
                            <span class="text-xs text-slate-400">
                                {{ post.slug }}
                            </span>
                        </div>
                    </div>
                </article>
            </div>

            <div v-if="meta" class="mt-5 text-sm text-slate-500">
                Showing {{ filteredPosts.length }} of {{ meta.total }} published posts.
            </div>
        </section>
    </div>
</template>