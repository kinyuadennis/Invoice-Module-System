<script setup>
  import { ref, computed } from 'vue'
  import { Link, router, usePage } from '@inertiajs/vue3'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import Modal from '@/Components/UI/Modal.vue'
  import { useFormatting } from '@/composables/useFormatting'
  import { useStatusBadge } from '@/composables/useStatusBadge'
  
  const page = usePage()
  const { formatDateLong, formatNumber } = useFormatting()
  const { getStatusBadgeClass } = useStatusBadge()
  
  const props = defineProps({
    invoice: {
      type: Object,
      required: true
    }
  })
  
  const showEmailModal = ref(false)
  const showPaymentModal = ref(false)
  const sendingEmail = ref(false)
  const recordingPayment = ref(false)
  const downloadingPDF = ref(false)
  
  const companyInfo = {
    name: 'Your Company Name',
    address: '123 Business St, Suite 100, City, State 12345',
    email: 'info@yourcompany.com',
    phone: '+1 (555) 123-4567'
  }
  
  const emailForm = ref({
    to: props.invoice.client?.email || '',
    subject: `Invoice #${props.invoice.invoice_number} from ${companyInfo.name}`,
    message: `Dear ${props.invoice.client?.name || 'Client'},\n\nPlease find attached invoice #${props.invoice.invoice_number} for $${props.invoice.total}.\n\nThank you for your business!`
  })
  
  const paymentForm = ref({
    amount: props.invoice.amount_due || 0,
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: '',
    reference: ''
  })
  
  const user = computed(() => page.props.auth.user)
  
  const canEdit = computed(() => {
    if (!user.value) return false
    const role = user.value.role
    return (role === 'admin' || role === 'staff') && props.invoice.status !== 'paid'
  })
  
  const canSendEmail = computed(() => {
    if (!user.value) return false
    const role = user.value.role
    return role === 'admin' || role === 'staff'
  })
  
  const canRecordPayment = computed(() => {
    if (!user.value) return false
    const role = user.value.role
    return (role === 'admin' || role === 'staff') && props.invoice.amount_due > 0
  })
  
  const isOverdue = computed(() => {
    if (props.invoice.status === 'paid') return false
    if (!props.invoice.due_date) return false
    return new Date(props.invoice.due_date) < new Date()
  })
  
  const statusBadgeClasses = computed(() => {
    return getStatusBadgeClass(props.invoice.status)
  })
  
  const printInvoice = () => {
    window.print()
  }
  
  const downloadPDF = () => {
    downloadingPDF.value = true
    router.get(`/invoices/${props.invoice.id}/pdf`, {}, {
      onFinish: () => {
        downloadingPDF.value = false
      }
    })
  }
  
  const sendEmail = () => {
    sendingEmail.value = true
    router.post(`/invoices/${props.invoice.id}/send`, emailForm.value, {
      onSuccess: () => {
        showEmailModal.value = false
      },
      onFinish: () => {
        sendingEmail.value = false
      }
    })
  }
  
  const recordPayment = () => {
    recordingPayment.value = true
    router.post(`/invoices/${props.invoice.id}/payments`, paymentForm.value, {
      onSuccess: () => {
        showPaymentModal.value = false
      },
      onFinish: () => {
        recordingPayment.value = false
      }
    })
  }
  
  const formatDate = formatDateLong
</script>

<template>
    <AppLayout page-title="Invoice Details">
      <div class="max-w-4xl mx-auto">
        <!-- Header Actions -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <Link href="/invoices" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Invoices
          </Link>
  
          <div class="flex flex-wrap gap-2">
            <button
              @click="printInvoice"
              class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Print
            </button>
            
            <button
              @click="downloadPDF"
              class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
              :disabled="downloadingPDF"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              {{ downloadingPDF ? 'Downloading...' : 'Download PDF' }}
            </button>
  
            <button
              v-if="canSendEmail"
              @click="showEmailModal = true"
              class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Send Email
            </button>
  
            <Link
              v-if="canEdit"
              :href="`/invoices/${invoice.id}/edit`"
              class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </Link>
          </div>
        </div>
  
        <!-- Invoice Card -->
        <div id="invoice-content" class="bg-white rounded-lg shadow-lg">
          <!-- Invoice Header -->
          <div class="p-8 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
              <!-- Company Info -->
              <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">INVOICE</h1>
                <p class="text-gray-600">{{ companyInfo.name }}</p>
                <p class="text-sm text-gray-500">{{ companyInfo.address }}</p>
                <p class="text-sm text-gray-500">{{ companyInfo.email }}</p>
                <p class="text-sm text-gray-500">{{ companyInfo.phone }}</p>
              </div>
  
              <!-- Invoice Details -->
              <div class="text-right">
                <div class="mb-4">
                  <span :class="statusBadgeClasses" class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full">
                    {{ invoice.status?.toUpperCase() || 'DRAFT' }}
                  </span>
                </div>
                <div class="space-y-1">
                  <p class="text-sm text-gray-600">Invoice Number</p>
                  <p class="text-lg font-bold text-gray-900">#{{ invoice.invoice_number }}</p>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Billing Information -->
          <div class="p-8 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <!-- Bill To -->
              <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Bill To</h3>
                <div class="text-gray-900">
                  <p class="font-semibold text-lg">{{ invoice.client.name }}</p>
                  <p class="text-sm text-gray-600 mt-1">{{ invoice.client.email }}</p>
                  <p v-if="invoice.client.phone" class="text-sm text-gray-600">{{ invoice.client.phone }}</p>
                  <p v-if="invoice.client.address" class="text-sm text-gray-600 mt-2">{{ invoice.client.address }}</p>
                </div>
              </div>
  
              <!-- Invoice Dates -->
              <div>
                <div class="space-y-3">
                  <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Invoice Date</p>
                    <p class="text-gray-900 mt-1">{{ formatDate(invoice.date) }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Due Date</p>
                    <p :class="[isOverdue ? 'text-red-600 font-semibold' : 'text-gray-900', 'mt-1']">
                      {{ formatDate(invoice.due_date) }}
                      <span v-if="isOverdue" class="text-xs">(Overdue)</span>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Invoice Items -->
          <div class="p-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Invoice Items</h3>
            
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr class="bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="item in invoice.items" :key="item.id">
                    <td class="px-4 py-4 text-sm text-gray-900">{{ item.description }}</td>
                    <td class="px-4 py-4 text-sm text-gray-900 text-right">{{ item.quantity }}</td>
                    <td class="px-4 py-4 text-sm text-gray-900 text-right">${{ formatNumber(item.unit_price) }}</td>
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 text-right">${{ formatNumber(item.total) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
  
            <!-- Mobile View -->
            <div class="md:hidden space-y-4">
              <div v-for="item in invoice.items" :key="item.id" class="p-4 bg-gray-50 rounded-lg">
                <p class="font-medium text-gray-900 mb-2">{{ item.description }}</p>
                <div class="grid grid-cols-2 gap-2 text-sm">
                  <div>
                    <span class="text-gray-500">Quantity:</span>
                    <span class="text-gray-900 ml-2">{{ item.quantity }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Unit Price:</span>
                    <span class="text-gray-900 ml-2">${{ formatNumber(item.unit_price) }}</span>
                  </div>
                </div>
                <div class="mt-2 pt-2 border-t border-gray-200">
                  <span class="text-gray-500 text-sm">Total:</span>
                  <span class="text-gray-900 font-semibold ml-2">${{ formatNumber(item.total) }}</span>
                </div>
              </div>
            </div>
  
            <!-- Totals -->
            <div class="mt-8 flex justify-end">
              <div class="w-full max-w-sm space-y-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Subtotal:</span>
                  <span class="text-gray-900 font-medium">${{ formatNumber(invoice.subtotal) }}</span>
                </div>
                
                <div v-if="invoice.tax > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Tax ({{ invoice.tax_rate }}%):</span>
                  <span class="text-gray-900 font-medium">${{ formatNumber(invoice.tax) }}</span>
                </div>
                
                <div class="flex justify-between text-lg font-bold pt-3 border-t-2 border-gray-300">
                  <span class="text-gray-900">Total:</span>
                  <span class="text-blue-600">${{ formatNumber(invoice.total) }}</span>
                </div>
  
                <div v-if="invoice.amount_paid > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Amount Paid:</span>
                  <span class="text-green-600 font-medium">-${{ formatNumber(invoice.amount_paid) }}</span>
                </div>
  
                <div v-if="invoice.amount_due > 0" class="flex justify-between text-lg font-bold pt-3 border-t border-gray-200">
                  <span class="text-gray-900">Amount Due:</span>
                  <span :class="[isOverdue ? 'text-red-600' : 'text-gray-900']">${{ formatNumber(invoice.amount_due) }}</span>
                </div>
              </div>
            </div>
  
            <!-- Notes -->
            <div v-if="invoice.notes" class="mt-8 pt-6 border-t border-gray-200">
              <h4 class="text-sm font-semibold text-gray-900 mb-2">Notes</h4>
              <p class="text-sm text-gray-600 whitespace-pre-line">{{ invoice.notes }}</p>
            </div>
          </div>
  
          <!-- Footer -->
          <div class="px-8 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
            <p class="text-xs text-center text-gray-500">
              Thank you for your business! If you have any questions, please contact us at {{ companyInfo.email }}
            </p>
          </div>
        </div>
  
        <!-- Payment History -->
        <div v-if="invoice.payments && invoice.payments.length > 0" class="mt-8 bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Payment History</h2>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div
                v-for="payment in invoice.payments"
                :key="payment.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
              >
                <div>
                  <p class="font-medium text-gray-900">${{ formatNumber(payment.amount) }}</p>
                  <p class="text-sm text-gray-500">{{ formatDate(payment.payment_date) }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm text-gray-600">{{ payment.payment_method }}</p>
                  <p v-if="payment.reference" class="text-xs text-gray-500">Ref: {{ payment.reference }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Record Payment Button -->
        <div v-if="canRecordPayment" class="mt-6">
          <button
            @click="showPaymentModal = true"
            class="w-full sm:w-auto px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium"
          >
            Record Payment
          </button>
        </div>
      </div>
  
      <!-- Email Modal -->
      <Modal :show="showEmailModal" @close="showEmailModal = false">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Send Invoice via Email</h3>
          <form @submit.prevent="sendEmail" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
              <input
                v-model="emailForm.to"
                type="email"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
              <input
                v-model="emailForm.subject"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
              <textarea
                v-model="emailForm.message"
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              ></textarea>
            </div>
            <div class="flex gap-3 pt-4">
              <button
                type="button"
                @click="showEmailModal = false"
                class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg"
                :disabled="sendingEmail"
              >
                {{ sendingEmail ? 'Sending...' : 'Send' }}
              </button>
            </div>
          </form>
        </div>
      </Modal>
  
      <!-- Payment Modal -->
      <Modal :show="showPaymentModal" @close="showPaymentModal = false">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Record Payment</h3>
          <form @submit.prevent="recordPayment" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
              <input
                v-model.number="paymentForm.amount"
                type="number"
                step="0.01"
                min="0"
                :max="invoice.amount_due"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <p class="mt-1 text-sm text-gray-500">Amount due: ${{ formatNumber(invoice.amount_due) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date *</label>
              <input
                v-model="paymentForm.payment_date"
                type="date"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
              <select
                v-model="paymentForm.payment_method"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select method...</option>
                <option value="cash">Cash</option>
                <option value="check">Check</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="credit_card">Credit Card</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Reference / Notes</label>
              <input
                v-model="paymentForm.reference"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div class="flex gap-3 pt-4">
              <button
                type="button"
                @click="showPaymentModal = false"
                class="flex-1 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg"
                :disabled="recordingPayment"
              >
                {{ recordingPayment ? 'Recording...' : 'Record Payment' }}
              </button>
            </div>
          </form>
        </div>
      </Modal>
    </AppLayout>
</template>
  
<style>
  @media print {
    body * {
      visibility: hidden;
    }
    #invoice-content,
    #invoice-content * {
      visibility: visible;
    }
    #invoice-content {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
    }
  }
</style>