<script setup>
  import { ref, onMounted, onUnmounted } from 'vue'
  import { Link, router } from '@inertiajs/vue3'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import Modal from '@/Components/UI/Modal.vue'
  import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'
  import { useFormatting } from '@/composables/useFormatting'
  
  const { formatNumber } = useFormatting()
  
  const props = defineProps({
    clients: {
      type: Object,
      default: () => ({ data: [], links: [] })
    },
    stats: {
      type: Object,
      default: () => ({ total: 0, active: 0, totalRevenue: 0 })
    }
  })
  
  const loading = ref(false)
  const search = ref('')
  const activeMenu = ref(null)
  const showDeleteModal = ref(false)
  const deleteTarget = ref(null)
  
  let searchTimeout = null
  const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
      loading.value = true
      router.get('/clients', { search: search.value }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
          loading.value = false
        }
      })
    }, 500)
  }
  
  const toggleMenu = (clientId) => {
    activeMenu.value = activeMenu.value === clientId ? null : clientId
  }
  
  const confirmDelete = (client) => {
    deleteTarget.value = client
    showDeleteModal.value = true
    activeMenu.value = null
  }
  
  const deleteClient = () => {
    router.delete(`/clients/${deleteTarget.value.id}`, {
      onSuccess: () => {
        showDeleteModal.value = false
        deleteTarget.value = null
      }
    })
  }
  
  const getInitials = (name) => {
    if (!name) return 'NA'
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
  }
  
  const handleClickOutside = (e) => {
    if (!e.target.closest('button')) {
      activeMenu.value = null
    }
  }
  
  onMounted(() => {
    document.addEventListener('click', handleClickOutside)
  })
  
  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    if (searchTimeout) {
      clearTimeout(searchTimeout)
    }
  })
</script>

<template>
    <AppLayout page-title="Clients">
      <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Clients</h2>
            <p class="mt-1 text-sm text-gray-600">Manage your client information</p>
          </div>
          <Link
            href="/clients/create"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Client
          </Link>
        </div>
  
        <!-- Search -->
        <div class="bg-white rounded-lg shadow p-4">
          <div class="relative">
            <input
              v-model="search"
              @input="debouncedSearch"
              type="text"
              placeholder="Search clients by name or email..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
  
        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Total Clients</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Active Clients</p>
            <p class="mt-2 text-3xl font-bold text-green-600">{{ stats.active }}</p>
          </div>
          <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
            <p class="mt-2 text-3xl font-bold text-blue-600">${{ formatNumber(stats.totalRevenue) }}</p>
          </div>
        </div>
  
        <!-- Clients Grid -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <LoadingSpinner size="lg" />
        </div>
  
        <div v-else-if="!clients.data || clients.data.length === 0" class="bg-white rounded-lg shadow py-12">
          <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No clients found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding a new client.</p>
            <div class="mt-6">
              <Link
                href="/clients/create"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg"
              >
                Add Client
              </Link>
            </div>
          </div>
        </div>
  
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="client in clients.data"
            :key="client.id"
            class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow"
          >
            <div class="p-6">
              <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                  <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-lg font-semibold text-blue-600">
                      {{ getInitials(client.name) }}
                    </span>
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ client.name }}</h3>
                    <p class="text-sm text-gray-500">{{ client.email }}</p>
                  </div>
                </div>
                <div class="relative">
                  <button
                    @click="toggleMenu(client.id)"
                    class="text-gray-400 hover:text-gray-600"
                  >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                  </button>
                  <div
                    v-if="activeMenu === client.id"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                  >
                    <Link
                      :href="`/clients/${client.id}/edit`"
                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                      Edit
                    </Link>
                    <Link
                      :href="`/invoices?client=${client.id}`"
                      class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                      View Invoices
                    </Link>
                    <button
                      @click="confirmDelete(client)"
                      class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
  
              <div class="mt-4 space-y-2">
                <div v-if="client.phone" class="flex items-center text-sm text-gray-600">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  {{ client.phone }}
                </div>
                <div v-if="client.address" class="flex items-center text-sm text-gray-600">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  {{ client.address }}
                </div>
              </div>
  
              <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm">
                  <span class="text-gray-500">Invoices:</span>
                  <span class="ml-1 font-semibold text-gray-900">{{ client.invoices_count }}</span>
                </div>
                <div class="text-sm">
                  <span class="text-gray-500">Total:</span>
                  <span class="ml-1 font-semibold text-green-600">${{ formatNumber(client.total_revenue) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Pagination -->
        <div v-if="clients.data && clients.data.length > 0" class="bg-white rounded-lg shadow px-4 py-3">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ clients.from }} to {{ clients.to }} of {{ clients.total }} results
            </div>
            <div class="flex space-x-2">
              <Link
                v-for="link in clients.links"
                :key="link.label"
                :href="link.url"
                :class="[
                  'px-4 py-2 text-sm font-medium rounded-lg border',
                  link.active
                    ? 'bg-blue-600 text-white border-blue-600'
                    : link.url
                    ? 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                    : 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
  
      <!-- Delete Modal -->
      <Modal :show="showDeleteModal" @close="showDeleteModal = false">
        <div class="p-6">
          <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="mt-4 text-lg font-medium text-gray-900 text-center">Delete Client</h3>
          <p class="mt-2 text-sm text-gray-500 text-center">
            Are you sure you want to delete {{ deleteTarget?.name }}? This will also delete all associated invoices. This action cannot be undone.
          </p>
          <div class="mt-6 flex space-x-3">
            <button
              @click="showDeleteModal = false"
              class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="deleteClient"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
              Delete
            </button>
          </div>
        </div>
      </Modal>
    </AppLayout>
</template>