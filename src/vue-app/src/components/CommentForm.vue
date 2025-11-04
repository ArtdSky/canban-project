<template>
  <form @submit.prevent="handleSubmit" class="mb-6">
    <div>
      <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
        Добавить комментарий
      </label>
      <textarea
        id="content"
        v-model="content"
        rows="3"
        required
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        placeholder="Введите комментарий..."
      ></textarea>
    </div>
    <div class="mt-2 flex justify-end">
      <button
        type="submit"
        :disabled="isLoading"
        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
      >
        {{ isLoading ? 'Отправка...' : 'Отправить' }}
      </button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useCommentsStore } from '@/stores/comments'

const props = defineProps<{
  taskId: number
}>()

const emit = defineEmits<{
  commentCreated: []
}>()

const commentsStore = useCommentsStore()
const content = ref('')
const { isLoading } = commentsStore

async function handleSubmit() {
  try {
    await commentsStore.createComment(props.taskId, { content: content.value })
    content.value = ''
    emit('commentCreated')
  } catch (error) {
    console.error('Ошибка создания комментария:', error)
  }
}
</script>

