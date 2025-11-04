<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Кнопка назад -->
        <router-link
          to="/"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
        >
          ← Назад к списку
        </router-link>

        <!-- Загрузка -->
        <div v-if="isLoading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2 text-gray-500">Загрузка задачи...</p>
        </div>

        <!-- Ошибка -->
        <div v-if="error && !isLoading" class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
          <p class="text-red-800">{{ error }}</p>
        </div>

        <!-- Детали задачи -->
        <div v-if="task && !isLoading" class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="flex justify-between items-start mb-4">
            <h1 class="text-2xl font-bold text-gray-900">{{ task.title }}</h1>
            <div class="flex space-x-2">
              <router-link
                :to="{ name: 'task-edit', params: { id: task.id } }"
                class="px-3 py-1 text-sm text-indigo-600 hover:text-indigo-800"
              >
                Редактировать
              </router-link>
            </div>
          </div>

          <p class="text-gray-700 mb-4">{{ task.description }}</p>

          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <span class="text-sm font-medium text-gray-500">Статус:</span>
              <span
                :class="[
                  'ml-2 px-2 py-1 text-xs font-medium rounded',
                  getStatusClass(task.status)
                ]"
              >
                {{ getStatusLabel(task.status) }}
              </span>
            </div>
            <div v-if="task.due_date">
              <span class="text-sm font-medium text-gray-500">Срок:</span>
              <span class="ml-2 text-sm text-gray-900">{{ formatDate(task.due_date) }}</span>
            </div>
          </div>

          <!-- Участники -->
          <div v-if="task.participants && task.participants.length > 0" class="mt-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Участники:</h3>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="participant in task.participants"
                :key="participant.id"
                class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded"
              >
                {{ participant.user.name }} ({{ getRoleLabel(participant.role) }})
              </span>
            </div>
          </div>
        </div>

        <!-- Комментарии -->
        <div v-if="task && !isLoading" class="bg-white rounded-lg shadow p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Комментарии</h2>
          
          <!-- Форма комментария -->
          <CommentForm :task-id="task.id" @comment-created="loadComments" />

          <!-- Список комментариев -->
          <CommentList :task-id="task.id" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useTasksStore } from '@/stores/tasks'
import CommentForm from '@/components/CommentForm.vue'
import CommentList from '@/components/CommentList.vue'

const route = useRoute()
const tasksStore = useTasksStore()

const { isLoading, error } = tasksStore
const task = computed(() => tasksStore.currentTask)

function getStatusLabel(status: string): string {
  const labels: Record<string, string> = {
    todo: 'К выполнению',
    in_progress: 'В работе',
    done: 'Выполнено',
    closed: 'Закрыто',
  }
  return labels[status] || status
}

function getStatusClass(status: string): string {
  const classes: Record<string, string> = {
    todo: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    done: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

function getRoleLabel(role: string): string {
  const labels: Record<string, string> = {
    creator: 'Постановщик',
    assignee: 'Исполнитель',
    observer: 'Наблюдатель',
  }
  return labels[role] || role
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

async function loadComments() {
  // Комментарии загружаются в компоненте CommentList
}

async function loadTask() {
  const taskId = parseInt(route.params.id as string)
  if (!taskId || isNaN(taskId)) {
    console.error('Неверный ID задачи:', route.params.id)
    return
  }
  
  // Загружаем задачу, если она еще не загружена или ID изменился
  const currentId = tasksStore.currentTask?.id
  console.log('Загрузка задачи:', { taskId, currentId, needsLoad: currentId !== taskId })
  
  if (currentId !== taskId) {
    try {
      await tasksStore.fetchTask(taskId)
      console.log('Задача загружена:', tasksStore.currentTask)
    } catch (err) {
      console.error('Ошибка загрузки задачи:', err)
    }
  }
}

// Загружаем задачу при монтировании
onMounted(() => {
  loadTask()
})

// Перезагружаем задачу при изменении ID в маршруте
watch(() => route.params.id, (newId, oldId) => {
  if (newId && newId !== oldId) {
    loadTask()
  }
}, { immediate: false })
</script>

