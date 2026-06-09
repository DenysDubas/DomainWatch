import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api'
import { unwrapCollection } from '@/api/helpers'
import type { CheckLog } from '@/types'

export const useLogsStore = defineStore('logs', () => {
  const entries = ref<CheckLog[]>([])
  const currentPage = ref(1)
  const lastPage = ref(1)
  const isLoading = ref(false)
  const isPaginating = ref(false)

  async function fetchForDomain(domainId: number, page = 1): Promise<void> {
    const isInitialLoad = entries.value.length === 0
    if (isInitialLoad) {
      isLoading.value = true
    } else {
      isPaginating.value = true
    }
    try {
      const { data } = await api.get(`/domains/${domainId}/logs?page=${page}`)
      entries.value = unwrapCollection<CheckLog>(data)
      currentPage.value = data.meta?.current_page ?? page
      lastPage.value = data.meta?.last_page ?? 1
    } finally {
      isLoading.value = false
      isPaginating.value = false
    }
  }

  function reset(): void {
    entries.value = []
    currentPage.value = 1
    lastPage.value = 1
    isLoading.value = false
    isPaginating.value = false
  }

  return { entries, currentPage, lastPage, isLoading, isPaginating, fetchForDomain, reset }
})
