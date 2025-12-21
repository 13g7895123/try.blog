<template>
  <div class="max-w-4xl mx-auto">
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
          <textarea
            id="content"
            v-model="form.content"
            required
            rows="15"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white font-mono"
            placeholder="# 開始撰寫..."
          ></textarea>
          
          <!-- Markdown 語法說明 -->
          <details class="mt-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 text-sm">
            <summary class="cursor-pointer font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600">
              📖 Markdown 語法說明 (點擊展開)
            </summary>
            <div class="mt-4 space-y-4 text-gray-600 dark:text-gray-400">
              <!-- 標題 -->
              <div>
                <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-1">標題</h4>
                <code class="block bg-gray-100 dark:bg-gray-800 p-2 rounded text-xs">
# 一級標題<br>
## 二級標題<br>
### 三級標題
                </code>
              </div>
              
              <!-- 圖片 -->
              <div>
                <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-1">📷 插入圖片</h4>
                <code class="block bg-gray-100 dark:bg-gray-800 p-2 rounded text-xs">
![圖片說明](https://example.com/image.jpg)
                </code>
                <p class="mt-1 text-xs text-gray-500">提示：您可以使用 Imgur、Cloudinary 等圖床服務上傳圖片後取得 URL</p>
              </div>
              
              <!-- 程式碼區塊 -->
              <div>
                <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-1">💻 程式碼區塊</h4>
                <code class="block bg-gray-100 dark:bg-gray-800 p-2 rounded text-xs whitespace-pre">
```javascript
function hello() {
  console.log('Hello, World!');
}
```</code>
                <p class="mt-1 text-xs text-gray-500">支援語法高亮：javascript, python, html, css, bash, json 等</p>
              </div>
              
              <!-- 行內程式碼 -->
              <div>
                <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-1">行內程式碼</h4>
                <code class="block bg-gray-100 dark:bg-gray-800 p-2 rounded text-xs">
使用 `const x = 1` 宣告變數
                </code>
              </div>
              
              <!-- 連結 -->
              <div>
                <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-1">🔗 連結</h4>
                <code class="block bg-gray-100 dark:bg-gray-800 p-2 rounded text-xs">
[連結文字](https://example.com)
                </code>
              </div>
              
              <!-- 列表 -->
              <div>
                <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-1">列表</h4>
                <code class="block bg-gray-100 dark:bg-gray-800 p-2 rounded text-xs">
- 項目 1<br>
- 項目 2<br>
<br>
1. 編號項目 1<br>
2. 編號項目 2
                </code>
              </div>
              
              <!-- 引用 -->
              <div>
                <h4 class="font-medium text-gray-800 dark:text-gray-200 mb-1">引用</h4>
                <code class="block bg-gray-100 dark:bg-gray-800 p-2 rounded text-xs">
&gt; 這是一段引用文字
                </code>
              </div>
            </div>
          </details>
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
