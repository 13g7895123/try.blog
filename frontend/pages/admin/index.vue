<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">儀表板</h2>
    
    <!-- 統計卡片 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border dark:border-gray-700">
        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">總文章數</h3>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ articleCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border dark:border-gray-700">
        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">總標籤數</h3>
        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ tagCount }}</p>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border dark:border-gray-700">
        <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium">系統狀態</h3>
        <p class="text-lg font-medium text-green-600 dark:text-green-400 mt-2">正常運作中</p>
      </div>
    </div>

    <!-- 最近文章 -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6">
      <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">最近文章</h3>
      <div v-if="loading" class="text-center py-4">載入中...</div>
      <div v-else-if="recentArticles.length === 0" class="text-gray-500">尚無文章</div>
      <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
        <li v-for="article in recentArticles" :key="article.id" class="py-3">
          <div class="flex justify-between items-center">
            <span class="text-gray-800 dark:text-gray-200">{{ article.title }}</span>
            <span class="text-sm text-gray-500">{{ formatDate(article.createdAt) }}</span>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

definePageMeta({
  layout: 'admin',
  middleware: ['auth']
})

const { getArticleSummaries } = usePost()
const { fetchTags } = useTag()

const loading = ref(true)
const articleCount = ref(0)
const tagCount = ref(0)
const recentArticles = ref<any[]>([])

onMounted(async () => {
  try {
    const [articles, tags] = await Promise.all([
      getArticleSummaries(),
      fetchTags()
    ])
    
    articleCount.value = articles.length
    tagCount.value = tags.length
    recentArticles.value = articles.slice(0, 5)
  } catch (e) {
    console.error('Failed to load dashboard data', e)
  } finally {
    loading.value = false
  }
})

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString('zh-TW')
}
</script>
