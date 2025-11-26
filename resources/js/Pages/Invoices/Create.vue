<script setup>
  import { ref, computed } from 'vue'
  import { Link, router, useForm } from '@inertiajs/vue3'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import Modal from '@/Components/UI/Modal.vue'
  import { useFormatting } from '@/composables/useFormatting'
  
  const { formatNumber } = useFormatting()
  
  const props = defineProps({
    invoice: Object,
    clients: {
      type: Array,
      default: () => []
    },
    errors: {
      type: Object,
      default: () => ({})
    }
  })
  
  const isEditing = computed(() => !!props.invoice)
  
  const form = useForm({
    invoice_number: props.invoice?.invoice_number || '',
    client_id: props.invoice?.client_id || '',
    date: props.invoice?.date || new Date().toISOString().split('T')[0],
    due_date: props.invoice?.due_date || '',
    status: props.invoice?.status || 'draft',
    items: props.invoice?.items || [],
    tax_rate: props.invoice?.tax_rate || 0,
    notes: props.invoice?.notes || ''
  })
  
  const processing = ref(false)
  const showNewClientModal = ref(false)
  const creatingClient = ref(false)
  
  const newClient = ref({
    name: '',
    email: '',
    phone: '',
    address: ''
  })
  
  const selectedClient = computed(() => {
    return props.clients.find(c => c.id === form.client_id)
  })
  
  const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (item.total || 0), 0)
  })
  
  const taxAmount = computed(() => {
    return subtotal.value * (form.tax_rate / 100)
  })
  
  const total = computed(() => {
    return subtotal.value + taxAmount.value
  })
  
  const addItem = () => {
    form.items.push({
      description: '',
      quantity: 1,
      unit_price: 0,
      total: 0
    })
  }
  
  const removeItem = (index) => {
    form.items.splice(index, 1)
    calculateTotals()
  }
  
  const calculateItemTotal = (index) => {
    const item = form.items[index]
    item.total = (item.quantity || 0) * (item.unit_price || 0)
    calculateTotals()
  }
  
  const calculateTotals = () => {
    // Trigger reactivity
    form.items = [...form.items]
  }
  
  const onClientChange = () => {
    // Additional logic when client changes if needed
  }
  
  const createClient = () => {
    creatingClient.value = true
    router.post('/clients', newClient.value, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        showNewClientModal.value = false
        newClient.value = { name: '', email: '', phone: '', address: '' }
        // Set the newly created client as selected
        const clients = page.props.clients
        if (clients && clients.length > 0) {
          form.client_id = clients[clients.length - 1].id
        }
      },
      onFinish: () => {
        creatingClient.value = false
      }
    })
  }
  
  const submitForm = () => {
    processing.value = true
    const url = isEditing.value ? `/invoices/${props.invoice.id}` : '/invoices'
    const method = isEditing.value ? 'put' : 'post'
    
    form[method](url, {
      onFinish: () => {
        processing.value = false
      }
    })
  }
  
  const saveAsDraft = () => {
    form.status = 'draft'
    submitForm()
  }
  
  // Initialize with one item if creating new invoice
  if (!isEditing.value && form.items.length === 0) {
    addItem()
  }
</script>

<template>
    <AppLayout :page-title="isEditing ? 'Edit Invoice' : 'Create Invoice'">
      <div class="max-w-5xl mx-auto">
        <div class="mb-6">
          <Link href="/invoices" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Invoices
          </Link>
          <h1 class="mt-2 text-3xl font-bold text-gray-900">{{ isEditing ? 'Edit Invoice' : 'Create New Invoice' }}</h1>
        </div>
  
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Invoice Details Card -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Invoice Details</h2>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Invoice Number -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Invoice Number <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.invoice_number"
                    type="text"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                      errors.invoice_number ? 'border-red-300' : 'border-gray-300'
                    ]"
                    placeholder="INV-001"
                  />
                  <p v-if="errors.invoice_number" class="mt-1 text-sm text-red-600">{{ errors.invoice_number }}</p>
                </div>
  
                <!-- Status -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Status <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.status"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                      errors.status ? 'border-red-300' : 'border-gray-300'
                    ]"
                  >
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                  <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</p>
                </div>
  
                <!-- Invoice Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Invoice Date <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.date"
                    type="date"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                      errors.date ? 'border-red-300' : 'border-gray-300'
                    ]"
                  />
                  <p v-if="errors.date" class="mt-1 text-sm text-red-600">{{ errors.date }}</p>
                </div>
  
                <!-- Due Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Due Date <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.due_date"
                    type="date"
                    :class="[
                      'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                      errors.due_date ? 'border-red-300' : 'border-gray-300'
                    ]"
                  />
                  <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">{{ errors.due_date }}</p>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Client Selection Card -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Client Information</h2>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <!-- Client Selection -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Client <span class="text-red-500">*</span>
                  </label>
                  <div class="flex gap-2">
                    <select
                      v-model="form.client_id"
                      @change="onClientChange"
                      :class="[
                        'flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                        errors.client_id ? 'border-red-300' : 'border-gray-300'
                      ]"
                    >
                      <option value="">Choose a client...</option>
                      <option v-for="client in clients" :key="client.id" :value="client.id">
                        {{ client.name }} - {{ client.email }}
                      </option>
                    </select>
                    <button
                      type="button"
                      @click="showNewClientModal = true"
                      class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium"
                    >
                      New Client
                    </button>
                  </div>
                  <p v-if="errors.client_id" class="mt-1 text-sm text-red-600">{{ errors.client_id }}</p>
                </div>
  
                <!-- Client Details Preview -->
                <div v-if="selectedClient" class="p-4 bg-gray-50 rounded-lg">
                  <h3 class="text-sm font-medium text-gray-900 mb-2">Client Details</h3>
                  <div class="text-sm text-gray-600 space-y-1">
                    <p><span class="font-medium">Name:</span> {{ selectedClient.name }}</p>
                    <p><span class="font-medium">Email:</span> {{ selectedClient.email }}</p>
                    <p v-if="selectedClient.phone"><span class="font-medium">Phone:</span> {{ selectedClient.phone }}</p>
                    <p v-if="selectedClient.address"><span class="font-medium">Address:</span> {{ selectedClient.address }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Invoice Items Card -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">Invoice Items</h2>
              <button
                type="button"
                @click="addItem"
                class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Item
              </button>
            </div>
            <div class="p-6">
              <div v-if="form.items.length === 0" class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p>No items added yet. Click "Add Item" to get started.</p>
              </div>
  
              <div v-else class="space-y-4">
                <!-- Mobile View -->
                <div class="lg:hidden space-y-4">
                  <div
                    v-for="(item, index) in form.items"
                    :key="index"
                    class="p-4 border border-gray-200 rounded-lg space-y-3"
                  >
                    <div class="flex items-center justify-between">
                      <span class="text-sm font-medium text-gray-700">Item {{ index + 1 }}</span>
                      <button
                        type="button"
                        @click="removeItem(index)"
                        class="text-red-600 hover:text-red-800"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                    
                    <input
                      v-model="item.description"
                      type="text"
                      placeholder="Item description"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    />
                    
                    <div class="grid grid-cols-3 gap-2">
                      <input
                        v-model.number="item.quantity"
                        @input="calculateItemTotal(index)"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="Qty"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                      />
                      <input
                        v-model.number="item.unit_price"
                        @input="calculateItemTotal(index)"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="Price"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                      />
                      <input
                        :value="formatNumber(item.total)"
                        readonly
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-700"
                      />
                    </div>
                  </div>
                </div>
  
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                  <table class="min-w-full">
                    <thead>
                      <tr class="border-b border-gray-200">
                        <th class="pb-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="pb-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Quantity</th>
                        <th class="pb-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Unit Price</th>
                        <th class="pb-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Total</th>
                        <th class="pb-3 w-12"></th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr v-for="(item, index) in form.items" :key="index">
                        <td class="py-3 pr-3">
                          <input
                            v-model="item.description"
                            type="text"
                            placeholder="Item description"
                            :class="[
                              'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                              errors[`items.${index}.description`] ? 'border-red-300' : 'border-gray-300'
                            ]"
                          />
                        </td>
                        <td class="py-3 pr-3">
                          <input
                            v-model.number="item.quantity"
                            @input="calculateItemTotal(index)"
                            type="number"
                            step="0.01"
                            min="0"
                            :class="[
                              'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                              errors[`items.${index}.quantity`] ? 'border-red-300' : 'border-gray-300'
                            ]"
                          />
                        </td>
                        <td class="py-3 pr-3">
                          <input
                            v-model.number="item.unit_price"
                            @input="calculateItemTotal(index)"
                            type="number"
                            step="0.01"
                            min="0"
                            :class="[
                              'w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                              errors[`items.${index}.unit_price`] ? 'border-red-300' : 'border-gray-300'
                            ]"
                          />
                        </td>
                        <td class="py-3 pr-3">
                          <input
                            :value="'$' + formatNumber(item.total)"
                            readonly
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-medium"
                          />
                        </td>
                        <td class="py-3">
                          <button
                            type="button"
                            @click="removeItem(index)"
                            class="text-red-600 hover:text-red-800 p-1"
                          >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
  
              <!-- Totals Section -->
              <div v-if="form.items.length > 0" class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex justify-end">
                  <div class="w-full max-w-xs space-y-3">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-600">Subtotal:</span>
                      <span class="font-medium text-gray-900">${{ formatNumber(subtotal) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center text-sm">
                      <span class="text-gray-600">Tax (%):</span>
                      <div class="flex items-center gap-2">
                        <input
                          v-model.number="form.tax_rate"
                          @input="calculateTotals"
                          type="number"
                          step="0.01"
                          min="0"
                          max="100"
                          class="w-20 px-3 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-right"
                        />
                        <span class="font-medium text-gray-900">${{ formatNumber(taxAmount) }}</span>
                      </div>
                    </div>
                    
                    <div class="flex justify-between text-lg font-bold pt-3 border-t border-gray-200">
                      <span class="text-gray-900">Total:</span>
                      <span class="text-blue-600">${{ formatNumber(total) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Notes -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-semibold text-gray-900">Additional Notes</h2>
            </div>
            <div class="p-6">
              <textarea
                v-model="form.notes"
                rows="4"
                placeholder="Add any additional notes or terms..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              ></textarea>
            </div>
          </div>
  
          <!-- Form Actions -->
          <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <Link
              href="/invoices"
              class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium text-center"
            >
              Cancel
            </Link>
            <button
              type="button"
              @click="saveAsDraft"
              class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium"
              :disabled="processing"
            >
              {{ processing ? 'Saving...' : 'Save as Draft' }}
            </button>
            <button
              type="submit"
              class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium"
              :disabled="processing || form.items.length === 0"
            >
              {{ processing ? 'Saving...' : (isEditing ? 'Update Invoice' : 'Create Invoice') }}
            </button>
          </div>
        </form>
      </div>
  
      <!-- New Client Modal -->
      <Modal :show="showNewClientModal" @close="showNewClientModal = false">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Add New Client</h3>
          <form @submit.prevent="createClient" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
              <input
                v-model="newClient.name"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
              <input
                v-model="newClient.email"
                type="email"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
              <input
                v-model="newClient.phone"
                type="tel"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
              <textarea
                v-model="newClient.address"
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              ></textarea>
            </div>
            <div class="flex gap-3 pt-4">
              <button
                type="button"
                @click="showNewClientModal = false"
                class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg"
                :disabled="creatingClient"
              >
                {{ creatingClient ? 'Creating...' : 'Create Client' }}
              </button>
            </div>
          </form>
        </div>
      </Modal>
    </AppLayout>
  </template>
  
  