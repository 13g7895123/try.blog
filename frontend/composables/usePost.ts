import { ref, computed } from 'vue'
import type { Article, CreateArticleInput, UpdateArticleInput, ArticleSummary } from '~/types/article'
import type { Tag } from '~/types/tag'
import { useApi } from './useApi'
import { NotFoundError } from '~/types/api'

export function usePost() {
  const api = useApi()

  // 狀態
  const articles = ref<Article[]>([])
  const currentArticle = ref<Article | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  /**
   * 取得所有文章列表
   */
  async function fetchArticles(): Promise<Article[]> {
    loading.value = true
    error.value = null

    try {
      const data = await api.get<Article[]>('articles')
      articles.value = data
      return data
    } catch (e) {
      const message = e instanceof Error ? e.message : '無法取得文章列表'
      error.value = message
      console.error('fetchArticles 失敗:', e)
      return []
    } finally {
      loading.value = false
    }
  }

  /**
   * 取得單篇文章
   */
  async function getArticle(id: string): Promise<Article> {
    loading.value = true
    error.value = null

    try {
      const article = await api.get<Article>(`articles/${id}`)
      currentArticle.value = article
      return article
    } catch (e) {
      if (e instanceof NotFoundError) {
        error.value = '找不到文章'
        throw e
      }
      const message = e instanceof Error ? e.message : '無法取得文章'
      error.value = message
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * 建立新文章
   */
  async function createArticle(input: CreateArticleInput): Promise<Article> {
    loading.value = true
    error.value = null

    try {
      const article = await api.post<Article>('articles', input)
      articles.value.unshift(article)
      return article
    } catch (e) {
      const message = e instanceof Error ? e.message : '無法建立文章'
      error.value = message
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * 更新現有文章
   */
  async function updateArticle(id: string, input: UpdateArticleInput): Promise<Article> {
    loading.value = true
    error.value = null

    try {
      const updated = await api.put<Article>(`articles/${id}`, input)

      // 更新列表中的文章
      const index = articles.value.findIndex(a => a.id === id)
      if (index !== -1) {
        articles.value[index] = updated
      }

      currentArticle.value = updated
      return updated
    } catch (e) {
      const message = e instanceof Error ? e.message : '無法更新文章'
      error.value = message
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * 刪除文章
   */
  async function deleteArticle(id: string): Promise<void> {
    loading.value = true
    error.value = null

    try {
      await api.remove(`articles/${id}`)

      // 移除列表中的文章
      articles.value = articles.value.filter(a => a.id !== id)

      if (currentArticle.value?.id === id) {
        currentArticle.value = null
      }
    } catch (e) {
      const message = e instanceof Error ? e.message : '無法刪除文章'
      error.value = message
      throw e
    } finally {
      loading.value = false
    }
  }

  /**
   * 取得文章摘要列表（用於列表頁面）
   */
  async function getArticleSummaries(): Promise<ArticleSummary[]> {
    loading.value = true
    try {
      const summaries = await api.get<ArticleSummary[]>('articles/summary')
      return summaries
    } catch (e) {
      console.error('getArticleSummaries 失敗:', e)
      return []
    } finally {
      loading.value = false
    }
  }

  return {
    articles: computed(() => articles.value),
    currentArticle: computed(() => currentArticle.value),
    loading: computed(() => loading.value),
    error: computed(() => error.value),
    fetchArticles,
    getArticle,
    createArticle,
    updateArticle,
    deleteArticle,
    getArticleSummaries,
    // 相容舊介面 (mock)
    getArticlesByTag: async () => []
  }
}
