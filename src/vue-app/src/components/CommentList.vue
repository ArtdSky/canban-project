<template>
  <div>
    <div v-if="isLoading" class="text-center py-4">
      <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else-if="comments.length === 0" class="text-center py-8 text-gray-500">
      Нет комментариев
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="comment in comments"
        :key="comment.id"
        class="border-b border-gray-200 pb-4 last:border-0"
      >
        <div class="flex justify-between items-start mb-2">
          <div>
            <p class="text-sm font-medium text-gray-900">
              {{ comment.user?.name || 'Неизвестный пользователь' }}
            </p>
            <p class="text-xs text-gray-500">
              {{ formatDate(comment.created_at) }}
            </p>
          </div>
        </div>
        <p class="text-gray-700 whitespace-pre-wrap">{{ comment.content }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { useCommentsStore } from '@/stores/comments'

const props = defineProps<{
  taskId: number
}>()

const commentsStore = useCommentsStore()
const { comments, isLoading } = commentsStore

function formatDate(date: string): string {
  return new Date(date).toLocaleString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  commentsStore.fetchComments(props.taskId)
})

// Перезагружаем комментарии при изменении taskId
watch(() => props.taskId, () => {
  if (props.taskId) {
    commentsStore.fetchComments(props.taskId)
  }
})
</script>

