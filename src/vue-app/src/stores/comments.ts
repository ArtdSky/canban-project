import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import type { Comment, CommentFormData } from '@/types'

export const useCommentsStore = defineStore('comments', () => {
  // Храним комментарии по task_id в объекте для реактивности
  const commentsByTask = ref<Record<number, Comment[]>>({})
  const currentComment = ref<Comment | null>(null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Получить комментарии для задачи
  function getComments(taskId: number): Comment[] {
    return commentsByTask.value[taskId] || []
  }

  // Получить комментарии задачи
  async function fetchComments(taskId: number) {
    isLoading.value = true
    error.value = null
    try {
      const response = await api.get(`/tasks/${taskId}/comments`)
      const newComments = response.data.data || []
      
      // Сортируем по дате создания (новые сверху)
      newComments.sort((a: Comment, b: Comment) => {
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      })
      
      // Сохраняем комментарии для этой задачи (реактивно)
      commentsByTask.value = { ...commentsByTask.value, [taskId]: newComments }
      
      return newComments
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
      
      // Убеждаемся, что task_id установлен
      if (!comment.task_id) {
        comment.task_id = taskId
      }
      
      // Получаем текущие комментарии для этой задачи
      const taskComments = commentsByTask.value[taskId] || []
      
      // Проверяем, нет ли уже такого комментария (чтобы избежать дубликатов)
      const exists = taskComments.some(c => c.id === comment.id)
      if (!exists) {
        // Добавляем новый комментарий в начало списка для мгновенного отображения
        const updatedComments = [comment, ...taskComments]
        // Обновляем реактивно
        commentsByTask.value = { ...commentsByTask.value, [taskId]: updatedComments }
      }
      
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

      // Обновляем в списке комментариев для соответствующей задачи
      const taskId = comment.task_id
      if (taskId) {
        const taskComments = commentsByTask.value[taskId] || []
        const index = taskComments.findIndex(c => c.id === id)
        if (index !== -1) {
          const updatedComments = [...taskComments]
          updatedComments[index] = comment
          // Обновляем реактивно
          commentsByTask.value = { ...commentsByTask.value, [taskId]: updatedComments }
        }
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
      // Сначала нужно получить комментарий, чтобы узнать task_id
      let taskId: number | undefined
      if (currentComment.value?.id === id) {
        taskId = currentComment.value.task_id
      } else {
        // Ищем комментарий во всех задачах
        for (const [tid, taskComments] of Object.entries(commentsByTask.value)) {
          const found = taskComments.find(c => c.id === id)
          if (found) {
            taskId = found.task_id
            break
          }
        }
      }
      
      await api.delete(`/comments/${id}`)
      
      // Удаляем из списка комментариев для соответствующей задачи
      if (taskId) {
        const taskComments = commentsByTask.value[taskId] || []
        const filtered = taskComments.filter(c => c.id !== id)
        // Обновляем реактивно
        commentsByTask.value = { ...commentsByTask.value, [taskId]: filtered }
      }
      
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
    commentsByTask.value = {}
    currentComment.value = null
  }

  return {
    // Геттер для обратной совместимости (возвращает все комментарии)
    get comments() {
      return Object.values(commentsByTask.value).flat()
    },
    getComments,
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

