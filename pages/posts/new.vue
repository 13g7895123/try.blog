<template>
  <div class="min-h-screen bg-white dark:bg-gray-950 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <!-- 頁面標題 -->
      <div class="mb-8">
        <NuxtLink
          to="/"
          class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 mb-4 transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          回到首頁
        </NuxtLink>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">
          撰寫新文章
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          使用 Markdown 格式撰寫您的文章
        </p>
      </div>

      <!-- 編輯器 -->
      <ArticleEditor
        :is-loading="isLoading"
        :tags="tags"
        :show-tag-input="true"
        @submit="handleSubmit"
        @cancel="handleCancel"
      />

      <!-- 提示訊息 -->
      <div v-if="successMessage" class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
        <p class="text-sm text-green-800 dark:text-green-200">
          {{ successMessage }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { CreateArticleInput } from '~/types/article'
import type { Tag } from '~/types/tag'

// 使用 Nuxt 自動導入（這些類型會在運行時注入）
declare const definePageMeta: any
declare const usePost: any
declare const useTag: any
declare const navigateTo: any

definePageMeta({
  layout: 'default'
})

// 狀態
const isLoading = ref(false)
const successMessage = ref('')
const tags = ref<Tag[]>([])

// 取得 composables
const post = usePost()
const tag = useTag()
const { createArticle } = post
const { fetchTags } = tag

// 提交表單
const handleSubmit = async (data: CreateArticleInput) => {
  isLoading.value = true
  try {
    const article = await createArticle(data)
    successMessage.value = '文章已成功建立！正在導向...'

    // 延遲 1 秒後導向到文章頁面
    setTimeout(() => {
      navigateTo(`/posts/${article.id}`)
    }, 1000)
  } catch (error) {
    console.error('建立文章失敗:', error)
    const message = error instanceof Error ? error.message : '建立文章失敗，請重試'
    // 顯示錯誤訊息（由 ArticleEditor 元件顯示）
  } finally {
    isLoading.value = false
  }
}

const handleCancel = () => {
  const confirmed = confirm('確定要放棄編輯嗎？所有未儲存的內容將會遺失。')
  if (confirmed) {
    navigateTo('/')
  }
}

// 生命週期
onMounted(async () => {
  try {
    // 取得標籤列表
    const allTags = await fetchTags()
    tags.value = allTags
  } catch (error) {
    console.error('載入標籤失敗:', error)
    // 允許使用者繼續編輯，即使標籤載入失敗
  }
})
</script>

<style scoped>
/* 頁面特定樣式 */
</style>
