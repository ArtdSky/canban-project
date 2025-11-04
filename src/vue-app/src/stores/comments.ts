import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import type { Comment, CommentFormData } from '@/types'

export const useCommentsStore = defineStore('comments', () => {
  const comments = ref<Comment[]>([])
  const currentComment = ref<Comment | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Получить комментарии задачи
  async function fetchComments(taskId: number) {
    isLoading.value = true
    error.value = null
    try {
      const response = await api.get(`/tasks/${taskId}/comments`)
      comments.value = response.data.data || []
      return comments.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Ошибка загрузки комментариев'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Получить комментарий по ID
  async function fetchComment(id: number) {
    isLoading.value = true
    error.value = null
    try {
      const response = await api.get(`/comments/${id}`)
      currentComment.value = response.data.data
      return currentComment.value
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Ошибка загрузки комментария'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Создать комментарий
  async function createComment(taskId: number, data: CommentFormData) {
    isLoading.value = true
    error.value = null
    try {
      const response = await api.post(`/tasks/${taskId}/comments`, data)
      const comment = response.data.data
      comments.value.push(comment)
      return comment
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Ошибка создания комментария'
      if (err.response?.data?.errors) {
        throw err.response.data.errors
      }
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Обновить комментарий
  async function updateComment(id: number, data: CommentFormData) {
    isLoading.value = true
    error.value = null
    try {
      const response = await api.put(`/comments/${id}`, data)
      const comment = response.data.data

      // Обновляем в списке
      const index = comments.value.findIndex(c => c.id === id)
      if (index !== -1) {
        comments.value[index] = comment
      }

      // Обновляем текущий комментарий
      if (currentComment.value?.id === id) {
        currentComment.value = comment
      }

      return comment
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Ошибка обновления комментария'
      if (err.response?.data?.errors) {
        throw err.response.data.errors
      }
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Удалить комментарий
  async function deleteComment(id: number) {
    isLoading.value = true
    error.value = null
    try {
      await api.delete(`/comments/${id}`)
      comments.value = comments.value.filter(c => c.id !== id)
      if (currentComment.value?.id === id) {
        currentComment.value = null
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Ошибка удаления комментария'
      throw err
    } finally {
      isLoading.value = false
    }
  }

  // Очистить комментарии
  function clearComments() {
    comments.value = []
    currentComment.value = null
  }

  return {
    comments,
    currentComment,
    isLoading,
    error,
    fetchComments,
    fetchComment,
    createComment,
    updateComment,
    deleteComment,
    clearComments,
  }
})

