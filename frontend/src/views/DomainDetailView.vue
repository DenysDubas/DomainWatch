<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavBar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="isLoading" class="space-y-4">
        <div class="h-8 w-48 bg-gray-200 rounded animate-pulse"></div>
        <div class="card h-40 animate-pulse"></div>
      </div>

      <template v-else-if="domain">
        <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
          <div>
            <div class="flex items-center gap-3 mb-1">
              <RouterLink to="/domains" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </RouterLink>
              <h2 class="text-xl font-semibold text-gray-900">{{ domain.name }}</h2>
              <span :class="statusBadge(domain.last_status)">{{ domain.last_status ?? 'unknown' }}</span>
            </div>
            <p class="text-sm text-gray-400 ml-8">{{ domain.url }}</p>
          </div>
          <div class="flex gap-2">
            <button
              class="btn-secondary gap-2"
              :disabled="domainsStore.checkingId === domainId"
              @click="runCheck"
            >
              <ActionSpinner v-if="domainsStore.checkingId === domainId" />
              {{ domainsStore.checkingId === domainId ? 'Checking…' : 'Check now' }}
            </button>
            <button class="btn-secondary" :disabled="domainsStore.isSaving" @click="openEdit">Edit</button>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
          <div class="card text-center">
            <p class="text-xs text-gray-500 mb-1">Interval</p>
            <p class="font-semibold text-gray-800">{{ domain.check_interval }} min</p>
          </div>
          <div class="card text-center">
            <p class="text-xs text-gray-500 mb-1">Timeout</p>
            <p class="font-semibold text-gray-800">{{ domain.timeout }} sec</p>
          </div>
          <div class="card text-center">
            <p class="text-xs text-gray-500 mb-1">Method</p>
            <p class="font-semibold text-gray-800">{{ domain.method }}</p>
          </div>
          <div class="card text-center">
            <p class="text-xs text-gray-500 mb-1">Active</p>
            <p class="font-semibold" :class="domain.is_active ? 'text-green-600' : 'text-gray-400'">
              {{ domain.is_active ? 'Yes' : 'Paused' }}
            </p>
          </div>
        </div>

        <div class="card">
          <h3 class="font-semibold text-gray-900 mb-4">Check History</h3>
          <CheckLogTable
            :logs="logsStore.entries"
            :is-loading="logsStore.isLoading"
          />

          <div v-if="logsStore.lastPage > 1" class="mt-4 flex items-center justify-center gap-2">
            <button
              class="btn-secondary text-sm gap-1"
              :disabled="logsStore.currentPage === 1 || logsStore.isPaginating"
              @click="changePage(logsStore.currentPage - 1)"
            >
              <ActionSpinner v-if="logsStore.isPaginating" />
              Prev
            </button>
            <span class="text-sm text-gray-500">{{ logsStore.currentPage }} / {{ logsStore.lastPage }}</span>
            <button
              class="btn-secondary text-sm gap-1"
              :disabled="logsStore.currentPage === logsStore.lastPage || logsStore.isPaginating"
              @click="changePage(logsStore.currentPage + 1)"
            >
              <ActionSpinner v-if="logsStore.isPaginating" />
              Next
            </button>
          </div>
        </div>
      </template>

      <div v-else class="text-center py-20 text-gray-400">
        Domain not found.
      </div>
    </main>

    <DomainFormModal
      v-if="showModal && domain"
      :domain="domain"
      @close="showModal = false"
      @saved="onSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useDomainsStore } from '@/stores/domains'
import { useLogsStore } from '@/stores/logs'
import ActionSpinner from '@/components/ActionSpinner.vue'
import AppNavBar from '@/components/AppNavBar.vue'
import CheckLogTable from '@/components/CheckLogTable.vue'
import DomainFormModal from '@/components/DomainFormModal.vue'
import type { Domain } from '@/types'

const route = useRoute()
const domainsStore = useDomainsStore()
const logsStore = useLogsStore()

const domain = ref<Domain | null>(null)
const isLoading = ref(true)
const showModal = ref(false)

const domainId = Number(route.params.id)

function statusBadge(status: string | null): string {
  if (status === 'up') return 'badge-up'
  if (status === 'down') return 'badge-down'
  return 'badge-unknown'
}

async function loadData(page = 1): Promise<void> {
  try {
    domain.value = await domainsStore.fetchOne(domainId)
    await logsStore.fetchForDomain(domainId, page)
  } finally {
    isLoading.value = false
  }
}

async function runCheck(): Promise<void> {
  domain.value = await domainsStore.triggerCheck(domainId)
  await logsStore.fetchForDomain(domainId)
}

function openEdit(): void {
  showModal.value = true
}

async function onSaved(): Promise<void> {
  showModal.value = false
  domain.value = await domainsStore.fetchOne(domainId)
}

async function changePage(page: number): Promise<void> {
  await logsStore.fetchForDomain(domainId, page)
}

onMounted(() => loadData())
onUnmounted(() => logsStore.reset())
</script>
