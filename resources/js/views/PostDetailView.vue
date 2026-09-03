<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

import api from '../services/api';
import { useAuth } from '../state/auth';

const route = useRoute();
const auth = useAuth();
const post = ref(null);
const loading = ref(true);
const error = ref('');

const canEdit = computed(() => auth.state.user?.role === 'admin' && post.value);

function formatDate(value) {
    if (!value) {
        return 'Draft';
    }

    return new Intl.DateTimeFormat('en', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(value));
}

async function loadPost() {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.get(`/api/posts/${route.params.slug}`);
        post.value = response.data.data;
    } catch {
        error.value = 'The requested post could not be found.';
    } finally {
        loading.value = false;
    }
}

onMounted(loadPost);

watch(
    () => route.params.slug,
    async () => {
        await loadPost();
    },
);
</script>

<template>
    <div>
        <div v-if="loading" class="rounded-2xl border border-black/10 bg-white p-8 text-slate-600 shadow-sm">
            Loading story...
        </div>

        <div v-else-if="error" class="rounded-2xl border border-rose-200 bg-rose-50 p-8 text-rose-700 shadow-sm">
            {{ error }}
        </div>

        <article v-else class="rounded-2xl border border-black/10 bg-white p-6 shadow-[0_18px_40px_rgba(15,23,42,0.08)] lg:p-10">
            <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-slate-500">
                <span>{{ formatDate(post.published_at) }}</span>
                <span>{{ post.author?.name ?? 'Editorial desk' }}</span>
            </div>

            <h2 class="mt-4 text-4xl font-semibold leading-tight text-slate-900 lg:text-5xl">
                {{ post.title }}
            </h2>

            <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">
                {{ post.excerpt }}
            </p>

            <div class="prose mt-10 max-w-none prose-p:leading-8 prose-p:text-slate-700 prose-headings:text-slate-900 prose-strong:text-slate-900">
                <p>{{ post.body }}</p>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <RouterLink class="rounded-md border border-black/10 bg-white px-5 py-3 text-sm font-semibold text-slate-800 transition hover:bg-slate-50" :to="{ name: 'home' }">
                    Back to blog
                </RouterLink>
                <RouterLink
                    v-if="canEdit"
                    class="rounded-md bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    :to="{ name: 'post.edit', params: { slug: post.slug } }"
                >
                    Edit post
                </RouterLink>
            </div>
        </article>
    </div>
</template>