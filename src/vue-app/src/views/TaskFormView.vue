<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <router-link
          :to="isEdit ? { name: 'task-detail', params: { id: taskId } } : { name: 'tasks' }"
          class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4"
        >
          ← Назад
        </router-link>

        <div class="bg-white rounded-lg shadow p-6">
          <h1 class="text-2xl font-bold text-gray-900 mb-6">
            {{ isEdit ? 'Редактировать задачу' : 'Создать задачу' }}
          </h1>

          <form @submit.prevent="handleSubmit">
            <div class="space-y-4">
              <!-- Название -->
              <div>
                <label for="title" class="block text-sm font-medium text-gray-700">
                  Название *
                </label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title[0] }}</p>
              </div>

              <!-- Описание -->
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                  Описание *
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="4"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
              </div>

              <!-- Статус -->
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700">
                  Статус
                </label>
                <select
                  id="status"
                  v-model="form.status"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                  <option value="todo">К выполнению</option>
                  <option value="in_progress">В работе</option>
                  <option value="done">Выполнено</option>
                  <option value="closed">Закрыто</option>
                </select>
                <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status[0] }}</p>
              </div>

              <!-- Срок исполнения -->
              <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700">
                  Срок исполнения
                </label>
                <input
                  id="due_date"
                  v-model="form.due_date"
                  type="datetime-local"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">{{ errors.due_date[0] }}</p>
              </div>

              <!-- Исполнители -->
              <div>
                <label for="assignees" class="block text-sm font-medium text-gray-700 mb-2">
                  Исполнители
                </label>
                <select
                  id="assignees"
                  v-model="form.assignee_ids"
                  multiple
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  size="4"
                >
                  <option
                    v-for="user in users"
                    :key="user.id"
                    :value="user.id"
                  >
                    {{ user.name }} ({{ user.email }})
                  </option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Выберите несколько исполнителей (зажмите Ctrl/Cmd)</p>
                <p v-if="errors.assignee_ids" class="mt-1 text-sm text-red-600">{{ errors.assignee_ids[0] }}</p>
              </div>

              <!-- Наблюдатели -->
              <div>
                <label for="observers" class="block text-sm font-medium text-gray-700 mb-2">
                  Наблюдатели
                </label>
                <select
                  id="observers"
                  v-model="form.observer_ids"
                  multiple
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  size="4"
                >
                  <option
                    v-for="user in users"
                    :key="user.id"
                    :value="user.id"
                  >
                    {{ user.name }} ({{ user.email }})
                  </option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Выберите несколько наблюдателей (зажмите Ctrl/Cmd)</p>
                <p v-if="errors.observer_ids" class="mt-1 text-sm text-red-600">{{ errors.observer_ids[0] }}</p>
              </div>
            </div>

            <div v-if="error" class="mt-4 text-red-600 text-sm">
              {{ error }}
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <router-link
                :to="isEdit ? { name: 'task-detail', params: { id: taskId } } : { name: 'tasks' }"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                Отмена
              </router-link>
              <button
                type="submit"
                :disabled="isLoading"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                {{ isLoading ? 'Сохранение...' : 'Сохранить' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useRoute, useRouter } from 'vue-router'
import { useTasksStore } from '@/stores/tasks'
import { useUsersStore } from '@/stores/users'
import type { TaskFormData } from '@/types'

const route = useRoute()
const router = useRouter()
const tasksStore = useTasksStore()
const usersStore = useUsersStore()

const taskId = ref<number | null>(null)
const isEdit = ref(false)

const form = ref<TaskFormData>({
  title: '',
  description: '',
  status: 'todo',
  due_date: null,
  assignee_ids: [],
  observer_ids: [],
})

const error = ref<string>('')
const errors = ref<Record<string, string[]>>({})
const { isLoading } = storeToRefs(tasksStore)
// Используем storeToRefs для сохранения реактивности users
const { users } = storeToRefs(usersStore)

onMounted(async () => {
  // Загружаем список пользователей
  await usersStore.fetchUsers()

  if (route.name === 'task-edit') {
    isEdit.value = true
    taskId.value = parseInt(route.params.id as string)
    await tasksStore.fetchTask(taskId.value)
    
    if (tasksStore.currentTask) {
      const dueDate = tasksStore.currentTask.due_date
      
      // Извлекаем ID исполнителей и наблюдателей из участников
      const assigneeIds = tasksStore.currentTask.participants
        ?.filter(p => p.role === 'assignee')
        .map(p => p.user_id) || []
      
      const observerIds = tasksStore.currentTask.participants
        ?.filter(p => p.role === 'observer')
        .map(p => p.user_id) || []
      
      form.value = {
        title: tasksStore.currentTask.title,
        description: tasksStore.currentTask.description,
        status: tasksStore.currentTask.status,
        due_date: dueDate 
          ? new Date(dueDate).toISOString().slice(0, 16)
          : null,
        assignee_ids: assigneeIds,
        observer_ids: observerIds,
      }
    }
  }
})

// Преобразуем дату перед отправкой
async function handleSubmit() {
  error.value = ''
  errors.value = {}

  const submitData: TaskFormData = {
    title: form.value.title,
    description: form.value.description,
    status: form.value.status,
    due_date: form.value.due_date || null,
    assignee_ids: form.value.assignee_ids && form.value.assignee_ids.length > 0 ? form.value.assignee_ids : undefined,
    observer_ids: form.value.observer_ids && form.value.observer_ids.length > 0 ? form.value.observer_ids : undefined,
  }

  try {
    if (isEdit.value && taskId.value) {
      await tasksStore.updateTask(taskId.value, submitData)
    } else {
      await tasksStore.createTask(submitData)
    }
    router.push({ name: 'tasks' })
  } catch (err: any) {
    if (err.errors) {
      errors.value = err.errors
    } else {
      error.value = err.message || 'Ошибка сохранения задачи'
    }
  }
}
</script>

