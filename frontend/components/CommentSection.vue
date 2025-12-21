<template>
    <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
            留言區
        </h3>

        <!-- 留言列表 -->
        <div class="space-y-6 mb-10">
            <div v-if="loading" class="text-center py-4 text-gray-500">
                載入留言中...
            </div>
            <div v-else-if="comments.length === 0"
                class="text-center py-8 bg-gray-50 dark:bg-gray-800/50 rounded-lg text-gray-500">
                目前尚無留言，成為第一個留言的人吧！
            </div>
            <div v-for="comment in comments" :key="comment.id"
                class="flex gap-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <img :src="comment.user_avatar || `https://ui-avatars.com/api/?name=${comment.user_name}&background=random`"
                    :alt="comment.user_name" class="w-10 h-10 rounded-full flex-shrink-0">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="font-bold text-gray-900 dark:text-gray-100">
                            {{ comment.user_name }}
                        </h4>
                        <span class="text-xs text-gray-500">
                            {{ formatDate(comment.created_at) }}
                        </span>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed whitespace-pre-line">
                        {{ comment.content }}
                    </p>
                </div>
            </div>
        </div>

        <!-- 留言表單 -->
        <div class="bg-gray-50 dark:bg-gray-800/30 p-6 rounded-xl border border-gray-200 dark:border-gray-700">
            <div v-if="!user" class="text-center py-4">
                <p class="mb-4 text-gray-600 dark:text-gray-400">登入 Google 帳號以發表留言</p>
                <div class="flex justify-center">
                    <GoogleLogin :callback="handleLoginSuccess" />
                </div>
            </div>

            <form v-else @submit.prevent="submitComment">
                <div class="flex items-center gap-3 mb-4">
                    <img :src="user.picture" :alt="user.name" class="w-8 h-8 rounded-full">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        以 {{ user.name }} 的身分留言
                    </span>
                    <button type="button" @click="logout"
                        class="text-xs text-red-500 hover:text-red-600 underline ml-auto">
                        登出
                    </button>
                </div>

                <textarea v-model="content" rows="3" required placeholder="分享您的想法..."
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white transition-shadow mb-3 resize-none"></textarea>

                <div class="flex justify-end">
                    <button type="submit" :disabled="submitting || !content.trim()"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span v-if="submitting" class="animate-spin">⏳</span>
                        送出留言
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { decodeCredential } from 'vue3-google-login'

const props = defineProps<{
    articleId: string
}>()

interface Comment {
    id: number
    user_name: string
    user_avatar: string
    content: string
    created_at: string
}

const comments = ref<Comment[]>([])
const loading = ref(true)
const submitting = ref(false)
const content = ref('')
const user = ref<any>(null)

// 載入留言
const fetchComments = async () => {
    try {
        const { data } = await useFetch<Comment[]>(`/api/articles/${props.articleId}/comments`)
        if (data.value) {
            comments.value = data.value
        }
    } catch (e) {
        console.error('Failed to load comments', e)
    } finally {
        loading.value = false
    }
}

// Google 登入回調
const handleLoginSuccess = (response: any) => {
    const userData = decodeCredential(response.credential)
    user.value = userData
}

const logout = () => {
    user.value = null
    content.value = ''
}

// 送出留言
const submitComment = async () => {
    if (!user.value || !content.value.trim()) return

    submitting.value = true
    try {
        await $fetch(`/api/articles/${props.articleId}/comments`, {
            method: 'POST',
            body: {
                content: content.value,
                user_name: user.value.name,
                user_email: user.value.email,
                user_avatar: user.value.picture
            }
        })

        // 清空並重新載入
        content.value = ''
        await fetchComments()
    } catch (e) {
        alert('留言失敗，請稍後再試')
        console.error(e)
    } finally {
        submitting.value = false
    }
}

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('zh-TW', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

onMounted(() => {
    fetchComments()
})
</script>
