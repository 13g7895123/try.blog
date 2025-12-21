export default defineNuxtRouteMiddleware(async (to, from) => {
    const { user, fetchUser } = useAuth()

    // 如果尚未載入使用者資訊，嘗試獲取
    if (!user.value) {
        await fetchUser()
    }

    // 如果仍未登入，導向登入頁
    if (!user.value) {
        return navigateTo('/login')
    }
})
