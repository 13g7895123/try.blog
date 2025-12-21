<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800 dark:text-white">標籤管理</h2>
    </div>

    <!-- 新增標籤表單 -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6 mb-6">
      <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-4">新增標籤</h3>
      <form @submit.prevent="handleCreate" class="flex gap-4">
        <input
          v-model="newTagName"
          type="text"
          placeholder="輸入標籤名稱"
          required
          class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
        />
        <button
          type="submit"
          :disabled="creating"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          {{ creating ? '新增中...' : '新增' }}
        </button>
      </form>
    </div>

    <!-- 標籤列表 -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">名稱</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Slug</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase hidden sm:table-cell">文章數</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">操作</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="loading">
            <td colspan="4" class="px-6 py-4 text-center text-gray-500">載入中...</td>
          </tr>
          <tr v-else-if="tagsWithCount.length === 0">
            <td colspan="4" class="px-6 py-4 text-center text-gray-500">目前沒有標籤</td>
          </tr>
          <tr v-for="tag in tagsWithCount" :key="tag.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                {{ tag.name }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
              {{ tag.slug }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
              {{ tag.count }} 篇
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
              <button
                @click="confirmDelete(tag)"
                :disabled="tag.count > 0"
                :title="tag.count > 0 ? '有文章使用此標籤，無法刪除' : '刪除標籤'"
                class="text-red-600 hover:text-red-900 dark:text-red-400 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                刪除
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

definePageMeta({
  layout: 'admin',
  middleware: ['auth']
})

const { createTag, deleteTag, getTagsWithCount } = useTag()

const loading = ref(true)
const creating = ref(false)
const newTagName = ref('')
const tagsWithCount = ref<any[]>([])

const loadTags = async () => {
  loading.value = true
  try {
    tagsWithCount.value = await getTagsWithCount()
  } finally {
    loading.value = false
  }
}

const handleCreate = async () => {
  if (!newTagName.value.trim()) return
  
  creating.value = true
  try {
    await createTag({ name: newTagName.value.trim() })
    newTagName.value = ''
    await loadTags()
  } catch (e) {
    alert('新增失敗: ' + (e instanceof Error ? e.message : '未知錯誤'))
  } finally {
    creating.value = false
  }
}

const confirmDelete = async (tag: any) => {
  if (tag.count > 0) {
    alert('此標籤仍有文章使用，無法刪除')
    return
  }
  
  if (confirm(`確定要刪除標籤「${tag.name}」嗎？`)) {
    try {
      await deleteTag(tag.id)
      await loadTags()
    } catch (e) {
      alert('刪除失敗')
    }
  }
}

onMounted(() => {
  loadTags()
})
</script>
