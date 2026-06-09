import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api'
import { unwrapCollection, unwrapResource } from '@/api/helpers'
import type { Domain, DomainFormData } from '@/types'

function extractMessage(e: unknown): string {
  if (typeof e === 'object' && e !== null && 'response' in e) {
    const response = (e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }).response
    if (response?.data?.errors) {
      const first = Object.values(response.data.errors)[0]?.[0]
      if (first) return first
    }
    if (response?.data?.message) return response.data.message
  }
  if (e instanceof Error) return e.message
  return 'An unexpected error occurred.'
}

function sleep(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms))
}

export const useDomainsStore = defineStore('domains', () => {
  const list = ref<Domain[]>([])
  const isLoading = ref(false)
  const isSaving = ref(false)
  const deletingId = ref<number | null>(null)
  const checkingId = ref<number | null>(null)
  const error = ref<string | null>(null)

  async function fetchAll(): Promise<void> {
    isLoading.value = true
    error.value = null
    try {
      const { data } = await api.get('/domains')
      list.value = unwrapCollection<Domain>(data)
    } catch (e: unknown) {
      error.value = extractMessage(e)
      throw e
    } finally {
      isLoading.value = false
    }
  }

  async function fetchOne(id: number): Promise<Domain> {
    const { data } = await api.get(`/domains/${id}`)
    return unwrapResource<Domain>(data)
  }

  async function create(payload: DomainFormData): Promise<Domain> {
    isSaving.value = true
    error.value = null
    try {
      const { data } = await api.post('/domains', payload)
      const domain = unwrapResource<Domain>(data)
      list.value = [domain, ...list.value.filter((d) => d.id !== domain.id)]
      return domain
    } catch (e: unknown) {
      error.value = extractMessage(e)
      throw e
    } finally {
      isSaving.value = false
    }
  }

  async function update(id: number, payload: DomainFormData): Promise<Domain> {
    isSaving.value = true
    error.value = null
    try {
      const { data } = await api.put(`/domains/${id}`, payload)
      const domain = unwrapResource<Domain>(data)
      const idx = list.value.findIndex((d) => d.id === id)
      if (idx !== -1) list.value[idx] = domain
      return domain
    } catch (e: unknown) {
      error.value = extractMessage(e)
      throw e
    } finally {
      isSaving.value = false
    }
  }

  async function remove(id: number): Promise<void> {
    deletingId.value = id
    error.value = null
    try {
      await api.delete(`/domains/${id}`)
      list.value = list.value.filter((d) => d.id !== id)
    } catch (e: unknown) {
      error.value = extractMessage(e)
      throw e
    } finally {
      deletingId.value = null
    }
  }

  async function triggerCheck(id: number): Promise<Domain> {
    checkingId.value = id
    error.value = null
    const before = list.value.find((d) => d.id === id)?.last_checked_at ?? null

    try {
      await api.post(`/domains/${id}/check`)

      for (let attempt = 0; attempt < 20; attempt++) {
        await sleep(1000)
        const domain = await fetchOne(id)
        if (domain.last_checked_at && domain.last_checked_at !== before) {
          const idx = list.value.findIndex((d) => d.id === id)
          if (idx !== -1) list.value[idx] = domain
          return domain
        }
      }

      return await fetchOne(id)
    } catch (e: unknown) {
      error.value = extractMessage(e)
      throw e
    } finally {
      checkingId.value = null
    }
  }

  return {
    list,
    isLoading,
    isSaving,
    deletingId,
    checkingId,
    error,
    fetchAll,
    fetchOne,
    create,
    update,
    remove,
    triggerCheck,
  }
})
