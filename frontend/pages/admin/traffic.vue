<template>
    <div class="w-full py-8 px-4">
        <h1 class="text-3xl font-bold mb-8 text-gray-900 dark:text-white">流量記錄</h1>

        <!-- 篩選 -->
        <div class="mb-6 flex flex-wrap gap-4 items-center">
            <select v-model="selectedArticle" @change="loadLogs"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500">
                <option value="">全部文章</option>
                <option v-for="article in articles" :key="article.id" :value="article.id">
                    {{ article.title }}
                </option>
            </select>
            <span class="text-sm text-gray-500 dark:text-gray-400">
                共 {{ logs.length }} 筆記錄
            </span>
        </div>

        <!-- 表格 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                文章
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                IP 位址
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                User Agent
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                時間
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="loading">
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                載入中...
                            </td>
                        </tr>
                        <tr v-else-if="logs.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                尚無瀏覽記錄
                            </td>
                        </tr>
                        <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ log.article_title || '(已刪除)' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-gray-300">
                                {{ log.ip_address }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate"
                                :title="log.user_agent">
                                {{ log.user_agent }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(log.viewed_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

definePageMeta({
    layout: 'admin'
})

// 使用 Nuxt 自動導入
declare const definePageMeta: any
declare const $fetch: any

interface ViewLog {
    id: number
    article_id: string
    article_title: string
    ip_address: string
    user_agent: string
    viewed_at: string
}

interface Article {
    id: string
    title: string
}

const logs = ref<ViewLog[]>([])
const articles = ref<Article[]>([])
const selectedArticle = ref('')
const loading = ref(true)

// 載入文章列表
const loadArticles = async () => {
    try {
        const data = await $fetch('/api/articles')
        articles.value = data
    } catch (e) {
        console.error('載入文章列表失敗', e)
    }
}

// 載入瀏覽記錄
const loadLogs = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams()
        if (selectedArticle.value) {
            params.set('article_id', selectedArticle.value)
        }
        params.set('limit', '200')

        const data = await $fetch(`/api/views/logs?${params.toString()}`)
        logs.value = data
    } catch (e) {
        console.error('載入流量記錄失敗', e)
    } finally {
        loading.value = false
    }
}

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('zh-TW', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    })
}

onMounted(() => {
    loadArticles()
    loadLogs()
})
</script>
