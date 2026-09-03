<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import api from '../services/api';

const route = useRoute();
const router = useRouter();

const isEditing = computed(() => Boolean(route.params.slug));
const loading = ref(false);
const pageLoading = ref(false);
const error = ref('');

const form = reactive({
    title: '',
    excerpt: '',
    body: '',
    published_at: '',
});

function formatForInput(value) {
    if (!value) {
        return '';
    }

    const date = new Date(value);
    const offset = date.getTimezoneOffset() * 60000;

    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
}

function payload() {
    return {
        title: form.title,
        excerpt: form.excerpt,
        body: form.body,
        published_at: form.published_at ? new Date(form.published_at).toISOString() : null,
    };
}

function extractMessage(exception) {
    return (
        exception.response?.data?.message ??
        Object.values(exception.response?.data?.errors ?? {})
            .flat()
            .shift() ??
        'Unable to save the post.'
    );
}

async function loadPost() {
    if (!isEditing.value) {
        return;
    }

    pageLoading.value = true;

    try {
        const response = await api.get(`/api/posts/${route.params.slug}`);
        const post = response.data.data;

        form.title = post.title;
        form.excerpt = post.excerpt;
        form.body = post.body;
        form.published_at = formatForInput(post.published_at);
    } catch {
        error.value = 'Unable to load the selected post.';
    } finally {
        pageLoading.value = false;
    }
}

async function submitForm() {
    loading.value = true;
    error.value = '';

    try {
        const response = isEditing.value
            ? await api.put(`/api/posts/${route.params.slug}`, payload())
            : await api.post('/api/posts', payload());

        await router.push({ name: 'post.show', params: { slug: response.data.data.slug } });
    } catch (exception) {
        error.value = extractMessage(exception);
    } finally {
        loading.value = false;
    }
}

onMounted(loadPost);

watch(
    () => route.params.slug,
    async () => {
        error.value = '';
        await loadPost();
    },
);
</script>

<template>
    <section class="mx-auto max-w-4xl rounded-2xl border border-black/10 bg-white p-6 shadow-sm lg:p-8">
        <div v-if="pageLoading" class="rounded-2xl border border-black/10 bg-white p-8 text-slate-600">
            Loading editor...
        </div>

        <template v-else>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-500">
                {{ isEditing ? 'Edit post' : 'New post' }}
            </p>
            <h2 class="mt-4 text-3xl font-medium text-slate-900">
                {{ isEditing ? 'Update the story' : 'Publish a new story' }}
            </h2>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 lg:text-base">
                Keep the form focused on the content model the API expects. Admin-only access is enforced on both the frontend and backend.
            </p>

            <form class="mt-8 space-y-4" @submit.prevent="submitForm">
                <label class="block space-y-2">
                    <span class="text-sm text-slate-700">Title</span>
                    <input v-model="form.title" class="w-full rounded-md border border-black/10 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400" type="text" required />
                </label>

                <label class="block space-y-2">
                    <span class="text-sm text-slate-700">Excerpt</span>
                    <textarea v-model="form.excerpt" class="min-h-28 w-full rounded-md border border-black/10 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400" required></textarea>
                </label>

                <label class="block space-y-2">
                    <span class="text-sm text-slate-700">Body</span>
                    <textarea v-model="form.body" class="min-h-52 w-full rounded-md border border-black/10 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400" required></textarea>
                </label>

                <label class="block space-y-2">
                    <span class="text-sm text-slate-700">Published at</span>
                    <input v-model="form.published_at" class="w-full rounded-md border border-black/10 bg-white px-4 py-3 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-slate-400" type="datetime-local" />
                </label>

                <p v-if="error" class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ error }}
                </p>

                <div class="flex flex-wrap gap-3">
                    <button class="inline-flex items-center justify-center rounded-md bg-slate-950 px-5 py-3 font-semibold text-white shadow-sm transition hover:bg-slate-900 disabled:cursor-not-allowed disabled:opacity-60" type="submit" :disabled="loading">
                        {{ loading ? 'Saving...' : 'Save post' }}
                    </button>
                </div>
            </form>
        </template>
    </section>
</template>