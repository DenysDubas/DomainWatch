export interface User {
  id: number
  name: string
  email: string
}

export interface Domain {
  id: number
  user_id: number
  url: string
  name: string
  check_interval: number
  timeout: number
  method: 'GET' | 'HEAD'
  is_active: boolean
  last_checked_at: string | null
  last_status: 'up' | 'down' | null
  last_response_code: number | null
  created_at: string
  updated_at: string
}

export interface CheckLog {
  id: number
  domain_id: number
  status: 'up' | 'down'
  response_code: number | null
  response_time: number
  error_message: string | null
  checked_at: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface DomainFormData {
  url: string
  name: string
  check_interval: number
  timeout: number
  method: 'GET' | 'HEAD'
  is_active: boolean
}
