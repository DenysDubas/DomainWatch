<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div
      class="absolute inset-0 bg-black/40 backdrop-blur-sm"
      @click="!domainsStore.isSaving && emit('close')"
    />

    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg">
      <div class="flex items-center justify-between p-6 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900 text-lg">
          {{ isEditing ? 'Edit Domain' : 'Add Domain' }}
        </h3>
        <button
          class="text-gray-400 hover:text-gray-600 transition-colors disabled:opacity-50"
          :disabled="domainsStore.isSaving"
          @click="emit('close')"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form class="p-6 space-y-4" @submit.prevent="handleSubmit">
        <fieldset :disabled="domainsStore.isSaving" class="space-y-4 disabled:opacity-60">
          <div>
            <label class="label">Name <span class="text-red-500">*</span></label>
            <input v-model="form.name" type="text" class="input" placeholder="My Website" required />
          </div>

          <div>
            <label class="label">URL <span class="text-red-500">*</span></label>
            <input v-model="form.url" type="url" class="input" placeholder="https://example.com" required />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Check Interval (min)</label>
              <input v-model.number="form.check_interval" type="number" class="input" min="1" max="1440" required />
            </div>
            <div>
              <label class="label">Timeout (sec)</label>
              <input v-model.number="form.timeout" type="number" class="input" min="1" max="60" required />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">HTTP Method</label>
              <select v-model="form.method" class="input">
                <option value="GET">GET</option>
                <option value="HEAD">HEAD</option>
              </select>
            </div>
            <div class="flex items-end pb-1">
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded accent-brand-600" />
                <span class="text-sm font-medium text-gray-700">Active monitoring</span>
              </label>
            </div>
          </div>
        </fieldset>

        <div v-if="errors.length" class="rounded-lg bg-red-50 border border-red-200 p-3 space-y-1">
          <p v-for="err in errors" :key="err" class="text-red-600 text-sm">{{ err }}</p>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button type="button" class="btn-secondary" :disabled="domainsStore.isSaving" @click="emit('close')">
            Cancel
          </button>
          <button type="submit" class="btn-primary gap-2" :disabled="domainsStore.isSaving">
            <ActionSpinner v-if="domainsStore.isSaving" />
            {{ domainsStore.isSaving ? 'Saving…' : (isEditing ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useDomainsStore } from '@/stores/domains'
import ActionSpinner from '@/components/ActionSpinner.vue'
import type { Domain, DomainFormData } from '@/types'

const props = defineProps<{ domain?: Domain | null }>()
const emit = defineEmits<{ close: []; saved: [] }>()

const domainsStore = useDomainsStore()
const errors = ref<string[]>([])

const isEditing = computed(() => !!props.domain?.id)

const defaultForm = (): DomainFormData => ({
  url: '',
  name: '',
  check_interval: 5,
  timeout: 10,
  method: 'GET',
  is_active: true,
})

const form = ref<DomainFormData>(defaultForm())

watch(
  () => props.domain,
  (d) => {
    if (d) {
      form.value = {
        url: d.url,
        name: d.name,
        check_interval: d.check_interval,
        timeout: d.timeout,
        method: d.method,
        is_active: d.is_active,
      }
    } else {
      form.value = defaultForm()
    }
  },
  { immediate: true },
)

async function handleSubmit(): Promise<void> {
  errors.value = []
  try {
    if (isEditing.value && props.domain) {
      await domainsStore.update(props.domain.id, form.value)
    } else {
      await domainsStore.create(form.value)
    }
    emit('saved')
  } catch (e: unknown) {
    errors.value = extractErrors(e)
  }
}

function extractErrors(e: unknown): string[] {
  if (e && typeof e === 'object' && 'response' in e) {
    const res = (e as { response?: { data?: { errors?: Record<string, string[]>; message?: string } } }).response
    if (res?.data?.errors) return Object.values(res.data.errors).flat()
    return [res?.data?.message ?? 'Failed to save domain.']
  }
  return ['Failed to save domain.']
}
</script>
