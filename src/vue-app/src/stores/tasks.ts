import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import type { Task, TaskFormData } from '@/types'

export const useTasksStore = defineStore('tasks', () => {
  const tasks = ref<Task[]>([])
  const currentTask = ref<Task | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Получить список задач с фильтрацией по статусу
  async function fetchTasks(status?: string) {
    isLoading.value = true
    error.value = null
    try {
      // Формируем параметры запроса
      // Если status undefined или пустая строка - не передаем параметр (все задачи)
      const params: Record<string, string> = {}
      if (status && status.trim() !== '') {
        params.status = status
      }
      
      console.log('API запрос /tasks с параметрами:', params)
      const response = await api.get('/tasks', { params })
      console.log('Получено задач:', response.data.data?.length || 0)
      tasks.value = response.data.data || []
      return tasks.value
    } catch (err: any) {
      console.error('Ошибка загрузки задач:', err)
      error.value = err.response?.data?.message || 'Ошибка загрузки задач'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Получить задачу по ID
  async function fetchTask(id: number) {
    isLoading.value = true
    error.value = null
    try {
      const response = await api.get(`/tasks/${id}`)
      currentTask.value = response.data.data
      return currentTask.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Ошибка загрузки задачи'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Создать задачу
  async function createTask(data: TaskFormData) {
    isLoading.value = true
    error.value = null
    try {
      const response = await api.post('/tasks', data)
      const task = response.data.data
      tasks.value.push(task)
      return task
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Ошибка создания задачи'
      if (err.response?.data?.errors) {
        throw err.response.data.errors
      }
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Обновить задачу
  async function updateTask(id: number, data: TaskFormData) {
    isLoading.value = true
    error.value = null
    try {
      const response = await api.put(`/tasks/${id}`, data)
      const task = response.data.data
      
      // Обновляем в списке
      const index = tasks.value.findIndex(t => t.id === id)
      if (index !== -1) {
        tasks.value[index] = task
      }
      
      // Обновляем текущую задачу
      if (currentTask.value?.id === id) {
        currentTask.value = task
      }
      
      return task
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Ошибка обновления задачи'
      if (err.response?.data?.errors) {
        throw err.response.data.errors
      }
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Удалить задачу
  async function deleteTask(id: number) {
    isLoading.value = true
    error.value = null
    try {
      await api.delete(`/tasks/${id}`)
      tasks.value = tasks.value.filter(t => t.id !== id)
      if (currentTask.value?.id === id) {
        currentTask.value = null
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Ошибка удаления задачи'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Очистить текущую задачу
  function clearCurrentTask() {
    currentTask.value = null
  }

  return {
    tasks,
    currentTask,
    isLoading,
    error,
    fetchTasks,
    fetchTask,
    createTask,
    updateTask,
    deleteTask,
    clearCurrentTask,
  }
})

