<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-brand-50 to-gray-100 px-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-600 text-white text-2xl mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Domain Monitor</h1>
        <p class="text-gray-500 text-sm mt-1">Sign in to your account</p>
      </div>

      <div class="card">
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div>
            <label class="label">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="input"
              placeholder="you@example.com"
              required
              autocomplete="email"
            />
          </div>

          <div>
            <label class="label">Password</label>
            <input
              v-model="form.password"
              type="password"
              class="input"
              placeholder="••••••••"
              required
              autocomplete="current-password"
            />
          </div>

          <p v-if="errorMsg" class="text-red-600 text-sm">{{ errorMsg }}</p>

          <button type="submit" class="btn-primary w-full gap-2" :disabled="auth.isLoading">
            <ActionSpinner v-if="auth.isLoading" />
            {{ auth.isLoading ? 'Signing in…' : 'Sign in' }}
          </button>
        </form>

        <p class="mt-5 text-center text-sm text-gray-500">
          No account?
          <RouterLink to="/register" class="text-brand-600 font-medium hover:underline">Create one</RouterLink>
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

const form = ref({ email: '', password: '' })
const errorMsg = ref('')

async function handleSubmit(): Promise<void> {
  errorMsg.value = ''
  try {
    await auth.login(form.value.email, form.value.password)
    router.push('/dashboard')
  } catch (e: unknown) {
    errorMsg.value = extractError(e)
  }
}

function extractError(e: unknown): string {
  if (e && typeof e === 'object' && 'response' in e) {
    const res = (e as { response?: { data?: { message?: string } } }).response
    return res?.data?.message ?? 'Login failed. Please try again.'
  }
  return 'Login failed. Please try again.'
}
</script>
