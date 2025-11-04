import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import type { User } from '@/types'

export const useUsersStore = defineStore('users', () => {
  const users = ref<User[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Получить список всех пользователей
  async function fetchUsers() {
    if (users.value.length > 0) {
      // Если уже загружены, возвращаем кэш
      return users.value
    }

    isLoading.value = true
    error.value = null
    try {
      const response = await api.get('/users')
      users.value = response.data.data || []
      return users.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Ошибка загрузки пользователей'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    users,
    isLoading,
    error,
    fetchUsers,
  }
})

