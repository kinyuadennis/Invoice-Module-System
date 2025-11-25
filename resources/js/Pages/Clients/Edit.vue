<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  client: Object,
  errors: Object
})

const isEditing = computed(() => !!props.client)

const form = useForm({
  name: props.client?.name || '',
  email: props.client?.email || '',
  phone: props.client?.phone || '',
  address: props.client?.address || '',
  company: props.client?.company || '',
  notes: props.client?.notes || ''
})

const processing = computed(() => form.processing)

const submit = () => {
  if (isEditing.value) {
    form.put(`/clients/${props.client.id}`)
  } else {
    form.post('/clients')
  }
}
</script>

<template>
    <AppLayout :page-title="isEditing ? 'Edit Client' : 'Add Client'">
      <div class="max-w-2xl mx-auto">
        <div class="mb-6">
          <Link href="/clients" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Clients
          </Link>
          <h1 class="mt-2 text-3xl font-bold text-gray-900">
            {{ isEditing ? 'Edit Client' : 'Add New Client' }}
          </h1>
        </div>
  
        <form @submit.prevent="submit" class="bg-white rounded-lg shadow">
          <div class="p-6 space-y-6">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Client Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                :class="[
                  'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                  errors.name ? 'border-red-300' : 'border-gray-300'
                ]"
                placeholder="Acme Corporation"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>
  
            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Email Address <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                :class="[
                  'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                  errors.email ? 'border-red-300' : 'border-gray-300'
                ]"
                placeholder="client@example.com"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
            </div>
  
            <!-- Phone -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Phone Number
              </label>
              <input
                v-model="form.phone"
                type="tel"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="+1 (555) 123-4567"
              />
            </div>
  
            <!-- Address -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Address
              </label>
              <textarea
                v-model="form.address"
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="123 Business St, Suite 100, City, State 12345"
              ></textarea>
            </div>
  
            <!-- Company -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Company
              </label>
              <input
                v-model="form.company"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Company name (optional)"
              />
            </div>
  
            <!-- Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Notes
              </label>
              <textarea
                v-model="form.notes"
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Additional notes about this client..."
              ></textarea>
            </div>
          </div>
  
          <!-- Actions -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex justify-end space-x-3">
            <Link
              href="/clients"
              class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50"
            >
              {{ processing ? 'Saving...' : (isEditing ? 'Update Client' : 'Add Client') }}
            </button>
          </div>
        </form>
      </div>
    </AppLayout>
</template>