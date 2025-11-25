<script setup>
  import { ref, computed, watch } from 'vue'
  import { Link, router, usePage } from '@inertiajs/vue3'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import Modal from '@/Components/UI/Modal.vue'
  
  const page = usePage()
  
  const props = defineProps({
    invoices: Object,
    stats: Object,
    filters: Object
  })
  
  const loading = ref(false)
  const showDeleteModal = ref(false)
  const deleteTarget = ref(null)
  
  const filters = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    dateRange: props.filters.dateRange || ''
  })
  
  const canCreateInvoice = computed(() => {
    const role = page.props.auth.user.role
    return role === 'admin' || role === 'staff'
  })
  
  const hasActiveFilters = computed(() => {
    return filters.value.status || filters.value.dateRange
  })
  
  let searchTimeout = null
  const debouncedSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
      applyFilters()
    }, 500)
  }
  
  const applyFilters = () => {
    loading.value = true
    router.get('/invoices', filters.value, {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        loading.value = false
      }
    })
  }
  
  const clearFilter = (key) => {
    filters.value[key] = ''
    applyFilters()
  }
  
  const clearAllFilters = () => {
    filters.value = { search: '', status: '', dateRange: '' }
    applyFilters()
  }
  
  const statusClasses = (status) => {
    const classes = {
      draft: 'bg-gray-100 text-gray-800',
      sent: 'bg-blue-100 text-blue-800',
      paid: 'bg-green-100 text-green-800',
      overdue: 'bg-red-100 text-red-800',
      cancelled: 'bg-gray-100 text-gray-600'
    }
    return classes[status] || classes.draft
  }
  
  const canEditInvoice = (invoice) => {
    const role = page.props.auth.user.role
    return (role === 'admin' || role === 'staff') && invoice.status !== 'paid'
  }
  
  const canDeleteInvoice = (invoice) => {
    const role = page.props.auth.user.role
    return role === 'admin' && invoice.status === 'draft'
  }
  
  const confirmDelete = (invoice) => {
    deleteTarget.value = invoice
    showDeleteModal.value = true
  }
  
  const deleteInvoice = () => {
    router.delete(`/invoices/${deleteTarget.value.id}`, {
      onSuccess: () => {
        showDeleteModal.value = false
        deleteTarget.value = null
      }
    })
  }
  
  const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  }
  
  const formatNumber = (num) => {
    return new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(num)
  }
</script>

<template>
    <AppLayout page-title="Invoices">
      <div class="space-y-6">
        <!-- Header with actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Invoices</h2>
            <p class="mt-1 text-sm text-gray-600">Manage and track all your invoices</p>
          </div>
          <Link
            v-if="canCreateInvoice"
            href="/invoices/create"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Invoice
          </Link>
        </div>
  
        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow">
          <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- Search -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                  <input
                    v-model="filters.search"
                    @input="debouncedSearch"
                    type="text"
                    placeholder="Search by invoice number or client..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>
  
              <!-- Status Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select
                  v-model="filters.status"
                  @change="applyFilters"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">All Statuses</option>
                  <option value="draft">Draft</option>
                  <option value="sent">Sent</option>
                  <option value="paid">Paid</option>
                  <option value="overdue">Overdue</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
  
              <!-- Date Range -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                <select
                  v-model="filters.dateRange"
                  @change="applyFilters"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">All Time</option>
                  <option value="today">Today</option>
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="quarter">This Quarter</option>
                  <option value="year">This Year</option>
                </select>
              </div>
            </div>
  
            <!-- Active filters -->
            <div v-if="hasActiveFilters" class="mt-4 flex items-center gap-2 flex-wrap">
              <span class="text-sm text-gray-600">Active filters:</span>
              <button
                v-if="filters.status"
                @click="clearFilter('status')"
                class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full hover:bg-blue-200"
              >
                Status: {{ filters.status }}
                <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              <button
                @click="clearAllFilters"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium"
              >
                Clear all
              </button>
            </div>
          </div>
        </div>
  
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Invoices</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total }}</p>
              </div>
              <div class="p-3 bg-blue-100 rounded-full">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
            </div>
          </div>
  
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Paid</p>
                <p class="mt-2 text-3xl font-bold text-green-600">${{ formatNumber(stats.paid) }}</p>
              </div>
              <div class="p-3 bg-green-100 rounded-full">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
  
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Outstanding</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">${{ formatNumber(stats.outstanding) }}</p>
              </div>
              <div class="p-3 bg-amber-100 rounded-full">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
  
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Overdue</p>
                <p class="mt-2 text-3xl font-bold text-red-600">${{ formatNumber(stats.overdue) }}</p>
              </div>
              <div class="p-3 bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Invoice Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          </div>
  
          <div v-else-if="invoices.data.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new invoice.</p>
            <div class="mt-6">
              <Link
                v-if="canCreateInvoice"
                href="/invoices/create"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg"
              >
                Create Invoice
              </Link>
            </div>
          </div>
  
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Link :href="`/invoices/${invoice.id}`" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                      #{{ invoice.invoice_number }}
                    </Link>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ invoice.client.name }}</div>
                    <div class="text-sm text-gray-500">{{ invoice.client.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(invoice.date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(invoice.due_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${{ formatNumber(invoice.total) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="statusClasses(invoice.status)" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                      {{ invoice.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <Link :href="`/invoices/${invoice.id}`" class="text-blue-600 hover:text-blue-900">View</Link>
                    <Link v-if="canEditInvoice(invoice)" :href="`/invoices/${invoice.id}/edit`" class="text-gray-600 hover:text-gray-900">Edit</Link>
                    <button v-if="canDeleteInvoice(invoice)" @click="confirmDelete(invoice)" class="text-red-600 hover:text-red-900">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
  
          <!-- Pagination -->
          <div v-if="invoices.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ invoices.from }}</span> to <span class="font-medium">{{ invoices.to }}</span> of <span class="font-medium">{{ invoices.total }}</span> results
              </div>
              <div class="flex space-x-2">
                <Link
                  v-for="link in invoices.links"
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
                  :disabled="!link.url"
                  v-html="link.label"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
  
      <!-- Delete Confirmation Modal -->
      <Modal :show="showDeleteModal" @close="showDeleteModal = false">
        <div class="p-6">
          <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="mt-4 text-lg font-medium text-gray-900 text-center">Delete Invoice</h3>
          <p class="mt-2 text-sm text-gray-500 text-center">
            Are you sure you want to delete invoice #{{ deleteTarget?.invoice_number }}? This action cannot be undone.
          </p>
          <div class="mt-6 flex space-x-3">
            <button
              @click="showDeleteModal = false"
              class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium"
            >
              Cancel
            </button>
            <button
              @click="deleteInvoice"
              class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium"
            >
              Delete
            </button>
          </div>
        </div>
      </Modal>
    </AppLayout>
  </template>
  
  