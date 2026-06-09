<template>
  <div class="min-h-screen bg-gray-50">
    <AppNavBar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="domainsStore.error" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ domainsStore.error }}
      </div>

      <h2 class="text-xl font-semibold text-gray-900 mb-6">Overview</h2>

      <div v-if="domainsStore.isLoading" class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div v-for="i in 3" :key="i" class="card animate-pulse h-28"></div>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="card">
          <p class="text-sm text-gray-500">Total Domains</p>
          <p class="text-4xl font-bold text-gray-900 mt-2">{{ stats.total }}</p>
        </div>
        <div class="card">
          <p class="text-sm text-gray-500">Online</p>
          <p class="text-4xl font-bold text-green-600 mt-2">{{ stats.up }}</p>
        </div>
        <div class="card">
          <p class="text-sm text-gray-500">Offline</p>
          <p class="text-4xl font-bold text-red-600 mt-2">{{ stats.down }}</p>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-gray-900">Recent Status</h3>
          <RouterLink to="/domains" class="text-brand-600 text-sm hover:underline">View all</RouterLink>
        </div>

        <div v-if="domainsStore.isLoading" class="space-y-3">
          <div v-for="i in 5" :key="i" class="h-12 bg-gray-100 rounded-lg animate-pulse"></div>
        </div>

        <div v-else-if="domainsStore.list.length === 0" class="text-center py-10 text-gray-400">
          <p class="mb-3">No domains yet.</p>
          <RouterLink to="/domains" class="btn-primary text-sm">Add first domain</RouterLink>
        </div>

        <table v-else class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 text-left text-gray-500">
              <th class="pb-3 font-medium">Domain</th>
              <th class="pb-3 font-medium">Status</th>
              <th class="pb-3 font-medium hidden sm:table-cell">Last Checked</th>
              <th class="pb-3 font-medium hidden sm:table-cell">Response</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="domain in recentDomains" :key="domain.id" class="hover:bg-gray-50 transition-colors">
              <td class="py-3 pr-4">
                <RouterLink :to="`/domains/${domain.id}`" class="font-medium text-gray-900 hover:text-brand-600">
                  {{ domain.name }}
                </RouterLink>
                <p class="text-xs text-gray-400 truncate max-w-xs">{{ domain.url }}</p>
              </td>
              <td class="py-3 pr-4">
                <span :class="statusBadgeClass(domain.last_status)">
                  {{ domain.last_status ?? 'unknown' }}
                </span>
              </td>
              <td class="py-3 pr-4 hidden sm:table-cell text-gray-500">
                {{ domain.last_checked_at ? formatTime(domain.last_checked_at) : '—' }}
              </td>
              <td class="py-3 hidden sm:table-cell text-gray-500">
                {{ domain.last_response_code ?? '—' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useDomainsStore } from '@/stores/domains'
import AppNavBar from '@/components/AppNavBar.vue'

const domainsStore = useDomainsStore()

const stats = computed(() => ({
  total: domainsStore.list.length,
  up: domainsStore.list.filter((d) => d.last_status === 'up').length,
  down: domainsStore.list.filter((d) => d.last_status === 'down').length,
}))

const recentDomains = computed(() => domainsStore.list.slice(0, 10))

function statusBadgeClass(status: string | null): string {
  if (status === 'up') return 'badge-up'
  if (status === 'down') return 'badge-down'
  return 'badge-unknown'
}

function formatTime(iso: string): string {
  return new Date(iso).toLocaleString()
}

onMounted(() => domainsStore.fetchAll())
</script>
