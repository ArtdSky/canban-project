export interface User {
  id: number
  name: string
  email: string
  email_verified_at?: string
  created_at: string
  updated_at: string
}

export interface TaskParticipant {
  id: number
  task_id: number
  user_id: number
  role: 'creator' | 'assignee' | 'observer'
  user: User
}

export interface Task {
  id: number
  title: string
  description: string
  status: string
  due_date: string | null
  created_at: string
  updated_at: string
  participants?: TaskParticipant[]
}

export interface Comment {
  id: number
  task_id: number
  user_id: number
  content: string
  status: string
  created_at: string
  updated_at: string
  user?: User
}

export interface TaskFormData {
  title: string
  description: string
  status?: string
  due_date?: string | null
  assignee_ids?: number[]
  observer_ids?: number[]
}

export interface CommentFormData {
  content: string
  status?: string
}

