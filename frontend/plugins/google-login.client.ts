export default defineNuxtPlugin((nuxtApp) => {
    import('vue3-google-login').then((module) => {
        nuxtApp.vueApp.use(module.default, {
            clientId: 'YOUR_GOOGLE_CLIENT_ID'
        })
    })
})
