<template>
  <div>
    <div v-if="isLoading" class="space-y-2">
      <div v-for="i in 5" :key="i" class="h-10 bg-gray-100 rounded animate-pulse"></div>
    </div>

    <div v-else-if="logs.length === 0" class="text-center py-10 text-gray-400 text-sm">
      No check history yet.
    </div>

    <table v-else class="w-full text-sm">
      <thead>
        <tr class="border-b border-gray-100 text-left text-gray-500">
          <th class="pb-3 font-medium">Time</th>
          <th class="pb-3 font-medium">Status</th>
          <th class="pb-3 font-medium hidden sm:table-cell">Code</th>
          <th class="pb-3 font-medium hidden sm:table-cell">Response Time</th>
          <th class="pb-3 font-medium">Error</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
          <td class="py-2.5 pr-4 text-gray-600 whitespace-nowrap">{{ formatTime(log.checked_at) }}</td>
          <td class="py-2.5 pr-4">
            <span :class="log.status === 'up' ? 'badge-up' : 'badge-down'">{{ log.status }}</span>
          </td>
          <td class="py-2.5 pr-4 hidden sm:table-cell text-gray-600">{{ log.response_code ?? '—' }}</td>
          <td class="py-2.5 pr-4 hidden sm:table-cell text-gray-600">{{ log.response_time.toFixed(0) }} ms</td>
          <td class="py-2.5 pr-4 max-w-xs">
            <span
              v-if="log.error_message"
              class="text-red-600 text-xs"
              :title="log.error_message"
            >
              {{ formatCheckError(log.error_message) }}
            </span>
            <span v-else class="text-gray-400">—</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import type { CheckLog } from '@/types'
import { formatCheckError } from '@/utils/formatError'

defineProps<{
  logs: CheckLog[]
  isLoading: boolean
}>()

function formatTime(iso: string): string {
  return new Date(iso).toLocaleString()
}
</script>
