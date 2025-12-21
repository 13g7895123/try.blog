<template>
  <div class="w-full">
    <div class="mb-6">
      <NuxtLink to="/admin/articles" class="text-gray-500 hover:text-gray-700 flex items-center mb-2">
        ← 返回列表
      </NuxtLink>
      <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
        {{ isEdit ? '編輯文章' : '新增文章' }}
      </h2>
    </div>

    <!-- 編輯器卡片 -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- 標題 -->
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            文章標題
          </label>
          <input
            id="title"
            v-model="form.title"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            placeholder="請輸入標題"
          />
        </div>

        <!-- 標籤 -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            標籤
          </label>
          <div class="flex flex-wrap gap-2 mb-2">
            <button
              v-for="tag in allTags"
              :key="tag.id"
              type="button"
              @click="toggleTag(tag.id)"
              :class="[
                'px-3 py-1 rounded-full text-sm font-medium transition-colors',
                form.tagIds.includes(tag.id)
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300'
              ]"
            >
              {{ tag.name }}
            </button>
          </div>
        </div>

        <!-- 內容 -->
        <div>
          <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            文章內容 (Markdown)
          </label>
          
          <!-- 工具列 -->
          <div class="flex gap-2 mb-2 p-2 bg-gray-50 dark:bg-gray-700/50 rounded-md border border-gray-200 dark:border-gray-600">
            <label class="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 rounded transition-colors">
              <input 
                type="file" 
                class="hidden" 
                accept="image/*"
                @change="handleImageUpload"
              >
              <span v-if="uploading">⏳ 上傳中...</span>
              <span v-else class="flex items-center gap-1">
                📷 插入圖片
              </span>
            </label>
          </div>

          <textarea
            id="content"
            ref="textareaRef"
            v-model="form.content"
            required
            rows="15"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white font-mono"
            placeholder="# 開始撰寫..."
          ></textarea>
        </div>

        <!-- 按鈕區 -->
        <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
          <NuxtLink
            to="/admin/articles"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            取消
          </NuxtLink>
          <button
            type="submit"
            :disabled="submitting"
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ submitting ? '儲存中...' : '發布文章' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'

definePageMeta({
  layout: 'admin',
  middleware: ['auth']
})

const route = useRoute()
const router = useRouter()
const { createArticle, updateArticle, getArticle } = usePost()
const { fetchTags } = useTag()

const isEdit = computed(() => route.path.includes('/edit/'))
const submitting = ref(false)
const allTags = ref<any[]>([])

const form = reactive({
  title: '',
  content: '',
  tagIds: [] as string[]
})

onMounted(async () => {
  // 載入標籤
  allTags.value = await fetchTags()

  // 如果是編輯模式，載入文章
  if (isEdit.value) {
    const id = route.params.id as string
    try {
      const article = await getArticle(id)
      form.title = article.title
      form.content = article.content
      form.tagIds = article.tagIds
    } catch (e) {
      alert('無法載入文章')
      router.push('/admin/articles')
    }
  }
})

const toggleTag = (id: string) => {
  const index = form.tagIds.indexOf(id)
  if (index === -1) {
    if (form.tagIds.length >= 5) return // 限制最多5個
    form.tagIds.push(id)
  } else {
    form.tagIds.splice(index, 1)
  }
}

const textareaRef = ref<HTMLTextAreaElement | null>(null)
const uploading = ref(false)
const { uploadImage } = useUpload()

const handleImageUpload = async (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files?.length) return

  const file = input.files[0]
  if (file.size > 2 * 1024 * 1024) {
    alert('圖片大小不能超過 2MB')
    return
  }

  uploading.value = true
  try {
    const url = await uploadImage(file)
    insertTextAtCursor(`![${file.name}](${url})\n`)
  } catch (e) {
    console.error(e)
    alert('圖片上傳失敗')
  } finally {
    uploading.value = false
    input.value = '' // Reset input
  }
}

const insertTextAtCursor = (text: string) => {
  if (!textareaRef.value) {
    form.content += text
    return
  }
  
  const textarea = textareaRef.value
  const start = textarea.selectionStart
  const end = textarea.selectionEnd
  
  form.content = 
    form.content.substring(0, start) + 
    text + 
    form.content.substring(end)
    
  // Restore cursor position after next tick
  setTimeout(() => {
    textarea.focus()
    textarea.selectionStart = textarea.selectionEnd = start + text.length
  }, 0)
}

const handleSubmit = async () => {
  if (!form.title || !form.content) return

  submitting.value = true
  try {
    if (isEdit.value) {
      await updateArticle(route.params.id as string, form)
    } else {
      await createArticle(form)
    }
    router.push('/admin/articles')
  } catch (e) {
    alert(isEdit.value ? '更新失敗' : '建立失敗')
  } finally {
    submitting.value = false
  }
}
</script>
