<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-gray-800 shadow-md flex-shrink-0 hidden md:flex flex-col">
      <div class="p-4 border-b dark:border-gray-700">
        <h1 class="text-xl font-bold text-gray-800 dark:text-white">部落格後台</h1>
      </div>
      <nav class="flex-1 p-4 space-y-1">
        <NuxtLink
          to="/admin"
          class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
          active-class="bg-blue-50 text-blue-700 dark:bg-gray-700 dark:text-white"
        >
          儀表板
        </NuxtLink>
        <NuxtLink
          to="/admin/articles"
          class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
          active-class="bg-blue-50 text-blue-700 dark:bg-gray-700 dark:text-white"
        >
          文章管理
        </NuxtLink>
        <NuxtLink
          to="/admin/tags"
          class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
          active-class="bg-blue-50 text-blue-700 dark:bg-gray-700 dark:text-white"
        >
          標籤管理
        </NuxtLink>
      </nav>
      <div class="p-4 border-t dark:border-gray-700">
        <button
          @click="handleLogout"
          class="w-full flex items-center px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition"
        >
          登出
        </button>
      </div>
    </aside>

    <!-- Mobile Header -->
    <div class="md:hidden fixed top-0 left-0 right-0 bg-white dark:bg-gray-800 z-50 border-b dark:border-gray-700 p-4 flex justify-between items-center">
      <h1 class="text-lg font-bold text-gray-800 dark:text-white">部落格後台</h1>
      <button @click="handleLogout" class="text-sm text-red-600">
        登出
      </button>
    </div>

    <!-- Main Content -->
    <main class="flex-1 overflow-auto p-4 md:p-8 mt-14 md:mt-0">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'

const { logout } = useAuth()

const handleLogout = async () => {
  await logout()
}
</script>
