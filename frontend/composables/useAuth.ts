import { ref } from 'vue'
import { useApi } from './useApi'
import type { User, LoginInput } from '~/types/user'

export function useAuth() {
    const api = useApi()
    const user = useState<User | null>('auth-user', () => null)
    const loading = ref(false)
    const error = ref<string | null>(null)

    /**
     * 登入
     */
    async function login(input: LoginInput): Promise<boolean> {
        loading.value = true
        error.value = null

        try {
            const response = await api.post<{ message: string, user: User }>('auth/login', input)
            user.value = response.user
            return true
        } catch (e) {
            error.value = e instanceof Error ? e.message : '登入失敗'
            return false
        } finally {
            loading.value = false
        }
    }

    /**
     * 登出
     */
    async function logout(): Promise<void> {
        try {
            await api.post('auth/logout', {})
            user.value = null
            navigateTo('/login')
        } catch (e) {
            console.error('登出失敗:', e)
        }
    }

    /**
     * 取得當前使用者資訊 (初始化用)
     */
    async function fetchUser(): Promise<void> {
        try {
            const userData = await api.get<User>('auth/me')
            user.value = userData
        } catch (e) {
            user.value = null
        }
    }

    return {
        user,
        loading,
        error,
        login,
        logout,
        fetchUser
    }
}
