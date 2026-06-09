<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavBar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="domainsStore.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ domainsStore.error }}
      </div>

      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Domains</h2>
        <button class="btn-primary gap-2" :disabled="domainsStore.isSaving" @click="openCreate">
          + Add Domain
        </button>
      </div>

      <div v-if="domainsStore.isLoading" class="space-y-3">
        <div v-for="i in 4" :key="i" class="card h-20 animate-pulse"></div>
      </div>

      <div v-else-if="domainsStore.list.length === 0" class="card text-center py-16 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
        </svg>
        <p class="mb-4">No domains added yet.</p>
        <button class="btn-primary" @click="openCreate">Add your first domain</button>
      </div>

      <div v-else class="card overflow-hidden p-0">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr class="text-left text-gray-500">
              <th class="px-6 py-3 font-medium">Domain</th>
              <th class="px-6 py-3 font-medium">Status</th>
              <th class="px-6 py-3 font-medium hidden md:table-cell">Interval</th>
              <th class="px-6 py-3 font-medium hidden md:table-cell">Method</th>
              <th class="px-6 py-3 font-medium hidden lg:table-cell">Last Checked</th>
              <th class="px-6 py-3 font-medium text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="domain in domainsStore.list"
              :key="domain.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-6 py-4">
                <RouterLink :to="`/domains/${domain.id}`" class="font-medium text-gray-900 hover:text-brand-600 block">
                  {{ domain.name }}
                </RouterLink>
                <span class="text-xs text-gray-400 truncate block max-w-xs">{{ domain.url }}</span>
              </td>
              <td class="px-6 py-4">
                <span :class="statusBadge(domain.last_status)">
                  {{ domain.last_status ?? 'unknown' }}
                </span>
                <span v-if="!domain.is_active" class="ml-2 badge-unknown">paused</span>
              </td>
              <td class="px-6 py-4 hidden md:table-cell text-gray-500">{{ domain.check_interval }}m</td>
              <td class="px-6 py-4 hidden md:table-cell text-gray-500">{{ domain.method }}</td>
              <td class="px-6 py-4 hidden lg:table-cell text-gray-500">
                {{ domain.last_checked_at ? formatTime(domain.last_checked_at) : '—' }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                  <button
                    class="btn-secondary text-xs px-2 py-1 gap-1"
                    :disabled="domainsStore.checkingId === domain.id"
                    @click="runCheck(domain.id)"
                  >
                    <ActionSpinner v-if="domainsStore.checkingId === domain.id" />
                    {{ domainsStore.checkingId === domain.id ? 'Checking…' : 'Check now' }}
                  </button>
                  <button
                    class="btn-secondary text-xs px-2 py-1"
                    :disabled="domainsStore.isSaving || domainsStore.deletingId === domain.id"
                    @click="openEdit(domain)"
                  >
                    Edit
                  </button>
                  <button
                    class="btn-danger text-xs px-2 py-1 gap-1"
                    :disabled="domainsStore.deletingId === domain.id"
                    @click="confirmDelete(domain.id)"
                  >
                    <ActionSpinner v-if="domainsStore.deletingId === domain.id" />
                    {{ domainsStore.deletingId === domain.id ? 'Deleting…' : 'Delete' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <DomainFormModal
      v-if="showModal"
      :domain="editingDomain"
      @close="closeModal"
      @saved="onSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useDomainsStore } from '@/stores/domains'
import ActionSpinner from '@/components/ActionSpinner.vue'
import AppNavBar from '@/components/AppNavBar.vue'
import DomainFormModal from '@/components/DomainFormModal.vue'
import type { Domain } from '@/types'

const domainsStore = useDomainsStore()
const showModal = ref(false)
const editingDomain = ref<Domain | null>(null)

function statusBadge(status: string | null): string {
  if (status === 'up') return 'badge-up'
  if (status === 'down') return 'badge-down'
  return 'badge-unknown'
}

function formatTime(iso: string): string {
  return new Date(iso).toLocaleString()
}

function openCreate(): void {
  editingDomain.value = null
  showModal.value = true
}

function openEdit(domain: Domain): void {
  editingDomain.value = domain
  showModal.value = true
}

function closeModal(): void {
  if (domainsStore.isSaving) return
  showModal.value = false
  editingDomain.value = null
}

function onSaved(): void {
  showModal.value = false
  editingDomain.value = null
}

async function runCheck(id: number): Promise<void> {
  await domainsStore.triggerCheck(id)
}

async function confirmDelete(id: number): Promise<void> {
  if (!confirm('Delete this domain and all its history?')) return
  await domainsStore.remove(id)
}

onMounted(() => domainsStore.fetchAll())
</script>
