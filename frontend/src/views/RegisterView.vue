<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-brand-50 to-gray-100 px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 text-white mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Domain Monitor</h1>
        <p class="text-gray-500 text-sm mt-1">Create your account</p>
      </div>

      <div class="card">
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label class="label">Name</label>
            <input v-model="form.name" type="text" class="input" placeholder="John Doe" required />
          </div>

          <div>
            <label class="label">Email</label>
            <input v-model="form.email" type="email" class="input" placeholder="you@example.com" required />
          </div>

          <div>
            <label class="label">Password</label>
            <input v-model="form.password" type="password" class="input" placeholder="min 8 characters" required />
          </div>

          <div>
            <label class="label">Confirm Password</label>
            <input v-model="form.passwordConfirmation" type="password" class="input" placeholder="repeat password" required />
          </div>

          <div v-if="errors.length" class="rounded-lg bg-red-50 border border-red-200 p-3 space-y-1">
            <p v-for="err in errors" :key="err" class="text-red-600 text-sm">{{ err }}</p>
          </div>

          <button type="submit" class="btn-primary w-full gap-2" :disabled="auth.isLoading">
            <ActionSpinner v-if="auth.isLoading" />
            {{ auth.isLoading ? 'Creating account…' : 'Create account' }}
          </button>
        </form>

        <p class="mt-5 text-center text-sm text-gray-500">
          Already have an account?
          <RouterLink to="/login" class="text-brand-600 font-medium hover:underline">Sign in</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ActionSpinner from '@/components/ActionSpinner.vue'

const auth = useAuthStore()
const router = useRouter()

const form = ref({ name: '', email: '', password: '', passwordConfirmation: '' })
const errors = ref<string[]>([])

async function handleSubmit(): Promise<void> {
  errors.value = []
  try {
    await auth.register(form.value.name, form.value.email, form.value.password, form.value.passwordConfirmation)
    router.push('/dashboard')
  } catch (e: unknown) {
    errors.value = extractErrors(e)
  }
}

function extractErrors(e: unknown): string[] {
  if (e && typeof e === 'object' && 'response' in e) {
    const res = (e as { response?: { data?: { errors?: Record<string, string[]>; message?: string } } }).response
    if (res?.data?.errors) {
      return Object.values(res.data.errors).flat()
    }
    return [res?.data?.message ?? 'Registration failed.']
  }
  return ['Registration failed.']
}
</script>
