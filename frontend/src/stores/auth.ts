import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import api from '@/api'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const user = ref<User | null>(null)
  const isLoading = ref(false)

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password: string): Promise<void> {
    isLoading.value = true
    try {
      const { data } = await api.post('/login', { email, password })
      setSession(data.token, data.user)
    } finally {
      isLoading.value = false
    }
  }

  async function register(
    name: string,
    email: string,
    password: string,
    passwordConfirmation: string,
  ): Promise<void> {
    isLoading.value = true
    try {
      const { data } = await api.post('/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      })
      setSession(data.token, data.user)
    } finally {
      isLoading.value = false
    }
  }

  async function logout(): Promise<void> {
    isLoading.value = true
    try {
      await api.post('/logout').catch(() => null)
      clearSession()
    } finally {
      isLoading.value = false
    }
  }

  async function fetchMe(): Promise<void> {
    const { data } = await api.get('/me')
    user.value = data
  }

  function setSession(newToken: string, newUser: User): void {
    token.value = newToken
    user.value = newUser
    localStorage.setItem('auth_token', newToken)
  }

  function clearSession(): void {
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')
  }

  return { token, user, isLoading, isAuthenticated, login, register, logout, fetchMe, clearSession }
})
