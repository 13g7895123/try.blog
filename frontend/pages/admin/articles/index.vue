<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800 dark:text-white">文章管理</h2>
      <NuxtLink
        to="/admin/articles/new"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
      >
        新增文章
      </NuxtLink>
    </div>

    <!-- 文章列表表格 -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                標題
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">
                標籤
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden md:table-cell">
                發布時間
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                操作
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-if="loading">
              <td colspan="4" class="px-6 py-4 text-center text-gray-500">載入中...</td>
            </tr>
            <tr v-else-if="articles.length === 0">
              <td colspan="4" class="px-6 py-4 text-center text-gray-500">目前沒有文章</td>
            </tr>
            <tr v-for="article in articles" :key="article.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ article.title }}</div>
                <div class="text-sm text-gray-500 truncate max-w-xs md:hidden">{{ article.excerpt }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="tag in article.tags"
                    :key="tag.id"
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                  >
                    {{ tag.name }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                {{ formatDate(article.createdAt) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <NuxtLink
                  :to="`/admin/articles/edit/${article.id}`"
                  class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-4"
                >
                  編輯
                </NuxtLink>
                <button
                  @click="confirmDelete(article.id)"
                  class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                >
                  刪除
                </button>
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
  layout: 'admin',
  middleware: ['auth']
})

const { getArticleSummaries, deleteArticle } = usePost()
const articles = ref<any[]>([])
const loading = ref(true)

const loadData = async () => {
  loading.value = true
  try {
    articles.value = await getArticleSummaries()
  } finally {
    loading.value = false
  }
}

const confirmDelete = async (id: string) => {
  if (confirm('確定要刪除這篇文章嗎？此操作無法復原。')) {
    try {
      await deleteArticle(id)
      await loadData() // Reload list
    } catch (e) {
      alert('刪除失敗')
    }
  }
}

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  loadData()
})
</script>
