<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Заголовок и фильтры -->
      <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Задачи</h1>
          <router-link
            to="/tasks/create"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            + Создать задачу
          </router-link>
        </div>

        <!-- Фильтр по статусу -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Фильтр по статусу:</label>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="status in statuses"
              :key="status.value || 'all'"
              @click="handleFilterChange(status.value)"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium transition-colors',
                selectedStatus === status.value
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
              ]"
            >
              {{ status.label }}
            </button>
          </div>
        </div>

        <!-- Состояние загрузки -->
        <div v-if="isLoading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          <p class="mt-2 text-gray-500">Загрузка задач...</p>
        </div>

        <!-- Ошибка -->
        <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
          <p class="text-red-800">{{ error }}</p>
        </div>

        <!-- Список задач -->
        <div v-else-if="tasks.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="task in tasks"
            :key="task.id"
            @click="goToTask(task.id)"
            class="bg-white rounded-lg shadow p-6 cursor-pointer hover:shadow-lg transition-shadow"
          >
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ task.title }}</h3>
            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ task.description }}</p>
            <div class="flex items-center justify-between">
              <span
                :class="[
                  'px-2 py-1 text-xs font-medium rounded',
                  getStatusClass(task.status)
                ]"
              >
                {{ getStatusLabel(task.status) }}
              </span>
              <span v-if="task.due_date" class="text-xs text-gray-500">
                {{ formatDate(task.due_date) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Пустое состояние -->
        <div v-else-if="!isLoading" class="text-center py-12">
          <p class="text-gray-500">Нет задач</p>
          <button
            @click="loadTasks"
            class="mt-4 px-4 py-2 text-sm text-indigo-600 hover:text-indigo-800"
          >
            Обновить
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, onActivated, nextTick } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter } from 'vue-router'
import { useTasksStore } from '@/stores/tasks'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const tasksStore = useTasksStore()
const authStore = useAuthStore()

const selectedStatus = ref<string | undefined>(undefined)
// Используем storeToRefs для сохранения реактивности
const { tasks, isLoading, error } = storeToRefs(tasksStore)

// Функция для загрузки задач
async function loadTasks() {
  // Пропускаем если уже загружается
  if (isLoading.value) {
    return
  }

  // Ждем инициализации auth если необходимо
  if (!authStore.user && authStore.token) {
    try {
      await authStore.init()
    } catch (err) {
      console.error('Ошибка инициализации auth:', err)
      return
    }
  }

  // Загружаем задачи если авторизован
  if (authStore.isAuthenticated) {
    // Передаем статус только если он не undefined
    // undefined означает "все задачи" и не передается в запрос
    const status = selectedStatus.value
    console.log('Загрузка задач со статусом:', status)
    await tasksStore.fetchTasks(status)
  }
}

const statuses = [
  { value: undefined, label: 'Все' },
  { value: 'todo', label: 'К выполнению' },
  { value: 'in_progress', label: 'В работе' },
  { value: 'done', label: 'Выполнено' },
  { value: 'closed', label: 'Закрыто' },
]

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

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('ru-RU')
}

function goToTask(id: number) {
  router.push({ name: 'task-detail', params: { id } })
}

// Обработчик изменения фильтра
async function handleFilterChange(status: string | undefined) {
  if (selectedStatus.value !== status) {
    selectedStatus.value = status
    // Загрузка будет выполнена через watch, не нужно вызывать здесь
  }
}

// Загрузка задач при изменении фильтра
watch(selectedStatus, async (newStatus, oldStatus) => {
  // Загружаем задачи при изменении фильтра
  if (newStatus !== oldStatus) {
    await loadTasks()
  }
}, { immediate: false })

onMounted(async () => {
  // Ждем завершения всех инициализаций
  await nextTick()

  // Если есть токен, но пользователь еще не загружен - инициализируем
  if (authStore.token && !authStore.user) {
    try {
      await authStore.init()
    } catch (err) {
      console.error('Ошибка инициализации auth:', err)
      return
    }
  }

  // Загружаем задачи если авторизован
  if (authStore.isAuthenticated) {
    await loadTasks()
  }
})

// Отслеживаем авторизацию и загружаем задачи при входе
watch(() => authStore.isAuthenticated, async (isAuth) => {
  if (isAuth) {
    await nextTick()
    await loadTasks()
  }
})

// Отслеживаем загрузку пользователя (для обновления страницы)
watch(() => authStore.user, async (user, prevUser) => {
  // Если пользователь только что загрузился (был null, стал не null)
  if (user && !prevUser && authStore.isAuthenticated) {
    await nextTick()
    await loadTasks()
  }
})

// Перезагружаем задачи при активации страницы (например, после логина)
onActivated(async () => {
  await nextTick()
  if (authStore.isAuthenticated) {
    await loadTasks()
  }
})
</script>

