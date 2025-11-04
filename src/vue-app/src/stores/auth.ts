import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import type { User } from '@/types'

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const isLoading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  // Загрузка текущего пользователя
  async function fetchUser() {
    try {
      const response = await api.get('/user')
      user.value = response.data.user || response.data.data
      return user.value
    } catch (error) {
      console.error('Ошибка загрузки пользователя:', error)
      throw error
    }
  }

  // Регистрация
  async function register(data: RegisterData) {
    isLoading.value = true
    try {
      await api.post('/register', data)
      // После регистрации автоматически авторизуем пользователя
      const loginResponse = await api.post('/login', {
        email: data.email,
        password: data.password,
      })
      token.value = loginResponse.data.token
      if (token.value) {
        localStorage.setItem('auth_token', token.value)
        await fetchUser()
      }
      return loginResponse.data
    } catch (error: any) {
      throw error.response?.data || error
    } finally {
      isLoading.value = false
    }
  }

  // Авторизация
  async function login(credentials: LoginCredentials) {
    isLoading.value = true
    try {
      const response = await api.post('/login', credentials)
      token.value = response.data.token
      if (token.value) {
        localStorage.setItem('auth_token', token.value)
        await fetchUser()
      }
      return response.data
    } catch (error: any) {
      throw error.response?.data || error
    } finally {
      isLoading.value = false
    }
  }

  // Выход
  async function logout() {
    isLoading.value = true
    try {
      await api.post('/logout')
    } catch (error) {
      console.error('Ошибка выхода:', error)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
      isLoading.value = false
    }
  }

  // Инициализация - проверяем токен и загружаем пользователя
  async function init() {
    if (token.value) {
      try {
        await fetchUser()
      } catch (error) {
        // Токен невалиден, очищаем
        token.value = null
        localStorage.removeItem('auth_token')
      }
    }
  }

  return {
    user,
    token,
    isLoading,
    isAuthenticated,
    register,
    login,
    logout,
    fetchUser,
    init,
  }
})

