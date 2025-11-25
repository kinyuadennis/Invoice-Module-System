<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

defineProps({
  errors: Object
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false
})

const processing = computed(() => form.processing)

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation')
  })
}
</script>

<template>
    <GuestLayout>
      <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
          <div>
            <div class="flex justify-center">
              <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
              Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
              Already have an account?
              <Link href="/login" class="font-medium text-blue-600 hover:text-blue-500">
                Sign in
              </Link>
            </p>
          </div>
  
          <form @submit.prevent="submit" class="mt-8 space-y-6">
            <div class="rounded-lg shadow-sm space-y-4">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                  Full Name
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  autocomplete="name"
                  required
                  :class="[
                    'mt-1 appearance-none relative block w-full px-3 py-2 border rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                    errors.name ? 'border-red-300' : 'border-gray-300'
                  ]"
                  placeholder="John Doe"
                />
                <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
              </div>
  
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                  Email address
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  autocomplete="email"
                  required
                  :class="[
                    'mt-1 appearance-none relative block w-full px-3 py-2 border rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                    errors.email ? 'border-red-300' : 'border-gray-300'
                  ]"
                  placeholder="you@example.com"
                />
                <p v-if="errors.email" class="mt-2 text-sm text-red-600">{{ errors.email }}</p>
              </div>
  
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                  Password
                </label>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  autocomplete="new-password"
                  required
                  :class="[
                    'mt-1 appearance-none relative block w-full px-3 py-2 border rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                    errors.password ? 'border-red-300' : 'border-gray-300'
                  ]"
                  placeholder="••••••••"
                />
                <p v-if="errors.password" class="mt-2 text-sm text-red-600">{{ errors.password }}</p>
                <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
              </div>
  
              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                  Confirm Password
                </label>
                <input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  autocomplete="new-password"
                  required
                  class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="••••••••"
                />
              </div>
            </div>
  
            <div class="flex items-center">
              <input
                id="terms"
                v-model="form.terms"
                type="checkbox"
                required
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="terms" class="ml-2 block text-sm text-gray-900">
                I agree to the
                <a href="#" class="text-blue-600 hover:text-blue-500">Terms of Service</a>
                and
                <a href="#" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
              </label>
            </div>
  
            <div>
              <button
                type="submit"
                :disabled="processing"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                  <svg v-if="!processing" class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                  </svg>
                  <svg v-else class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ processing ? 'Creating account...' : 'Create account' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </GuestLayout>
</template>