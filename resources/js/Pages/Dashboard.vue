<script setup>
  import { computed } from 'vue'
  import { Link, usePage } from '@inertiajs/vue3'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import { useFormatting } from '@/composables/useFormatting'
  import { useStatusBadge } from '@/composables/useStatusBadge'
  
  const page = usePage()
  const { formatDate, formatNumber } = useFormatting()
  const { getStatusBadgeClass } = useStatusBadge()
  
  const props = defineProps({
    stats: {
      type: Object,
      default: () => ({
        totalRevenue: 0,
        revenueChange: 0,
        totalPlatformFees: 0,
        outstanding: 0,
        outstandingCount: 0,
        overdue: 0,
        overdueCount: 0,
        paidCount: 0,
        totalClients: 0,
        activeClients: 0
      })
    },
    recentInvoices: {
      type: Array,
      default: () => []
    },
    statusDistribution: {
      type: Array,
      default: () => []
    },
    alerts: {
      type: Array,
      default: () => []
    }
  })
  
  const user = computed(() => page.props.auth.user)
  
  const canManageClients = computed(() => {
    if (!user.value) return false
    const role = user.value.role
    return role === 'admin' || role === 'staff'
  })
  </script>
  
  <template>
    <AppLayout page-title="Dashboard">
      <div class="space-y-6">
        <!-- Welcome Message -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg shadow-lg p-6 text-white">
          <h1 class="text-2xl font-bold mb-2">Welcome back, {{ user?.name || 'Guest' }}!</h1>
          <p class="text-blue-100">Here's an overview of your invoice activity</p>
        </div>
  
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total Revenue -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">${{ formatNumber(stats.totalRevenue) }}</p>
                <p class="mt-2 flex items-center text-sm">
                  <span :class="[stats.revenueChange >= 0 ? 'text-green-600' : 'text-red-600']" class="flex items-center">
                    <svg v-if="stats.revenueChange >= 0" class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    {{ Math.abs(stats.revenueChange) }}%
                  </span>
                  <span class="text-gray-500 ml-2">vs last month</span>
                </p>
              </div>
              <div class="p-3 bg-green-100 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
  
          <!-- Outstanding Amount -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Outstanding</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">${{ formatNumber(stats.outstanding) }}</p>
                <p class="mt-2 text-sm text-gray-500">{{ stats.outstandingCount }} invoices</p>
              </div>
              <div class="p-3 bg-amber-100 rounded-full">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>
  
          <!-- Overdue Amount -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Overdue</p>
                <p class="mt-2 text-3xl font-bold text-red-600">${{ formatNumber(stats.overdue) }}</p>
                <p class="mt-2 text-sm text-gray-500">{{ stats.overdueCount }} invoices</p>
              </div>
              <div class="p-3 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
            </div>
          </div>
  
          <!-- Total Clients -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Clients</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ stats.totalClients }}</p>
                <p class="mt-2 text-sm text-gray-500">{{ stats.activeClients }} active</p>
              </div>
              <div class="p-3 bg-blue-100 rounded-full">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Revenue Chart -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Overview</h3>
            <div class="h-64 flex items-center justify-center border border-gray-200 rounded-lg bg-gray-50">
              <div class="text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="text-sm">Chart visualization would go here</p>
                <p class="text-xs mt-1">Integrate with Chart.js or Recharts</p>
              </div>
            </div>
          </div>
  
          <!-- Invoice Status Distribution -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Invoice Status</h3>
            <div class="space-y-4">
              <div v-for="status in statusDistribution" :key="status.name" class="flex items-center justify-between">
                <div class="flex items-center flex-1">
                  <span :class="status.color" class="w-3 h-3 rounded-full mr-3"></span>
                  <span class="text-sm text-gray-700">{{ status.name }}</span>
                </div>
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-900 mr-4">{{ status.count }}</span>
                  <div class="w-32 bg-gray-200 rounded-full h-2">
                    <div :class="status.bgColor" class="h-2 rounded-full" :style="{ width: status.percentage + '%' }"></div>
                  </div>
                  <span class="text-sm text-gray-500 ml-2 w-12 text-right">{{ status.percentage }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Recent Invoices and Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Recent Invoices -->
          <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Recent Invoices</h3>
              <Link href="/invoices" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                View All
              </Link>
            </div>
            <div v-if="recentInvoices.length === 0" class="px-6 py-12 text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No invoices yet</h3>
              <p class="mt-1 text-sm text-gray-500">Get started by creating your first invoice.</p>
              <div class="mt-6">
                <Link
                  href="/invoices/create"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Create Invoice
                </Link>
              </div>
            </div>
            <div v-else class="divide-y divide-gray-200">
              <div
                v-for="invoice in recentInvoices"
                :key="invoice.id"
                class="px-6 py-4 hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <Link :href="`/invoices/${invoice.id}`" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                      #{{ invoice.invoice_number }}
                    </Link>
                    <p class="text-sm text-gray-600 mt-1">{{ invoice.client.name }}</p>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="text-right">
                      <p class="text-sm font-medium text-gray-900">${{ formatNumber(invoice.total) }}</p>
                      <p class="text-xs text-gray-500">{{ formatDate(invoice.due_date) }}</p>
                    </div>
                    <span :class="getStatusBadgeClass(invoice.status)" class="px-3 py-1 text-xs font-semibold rounded-full">
                      {{ invoice.status }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6 space-y-3">
              <Link
                href="/invoices/create"
                class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group"
              >
                <div class="p-2 bg-blue-600 rounded-lg group-hover:bg-blue-700 transition-colors">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-900">Create Invoice</span>
              </Link>
  
              <Link
                v-if="canManageClients"
                href="/clients/create"
                class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group"
              >
                <div class="p-2 bg-green-600 rounded-lg group-hover:bg-green-700 transition-colors">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                  </svg>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-900">Add Client</span>
              </Link>
  
              <Link
                href="/invoices?status=overdue"
                class="flex items-center p-3 bg-red-50 hover:bg-red-100 rounded-lg transition-colors group"
              >
                <div class="p-2 bg-red-600 rounded-lg group-hover:bg-red-700 transition-colors">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-900">View Overdue</span>
              </Link>
  
              <Link
                href="/profile"
                class="flex items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors group"
              >
                <div class="p-2 bg-gray-600 rounded-lg group-hover:bg-gray-700 transition-colors">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-900">Settings</span>
              </Link>
            </div>
          </div>
        </div>
  
        <!-- Alerts and Notifications -->
        <div v-if="alerts.length > 0" class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-900">Alerts</h3>
          <div
            v-for="alert in alerts"
            :key="alert.id"
            :class="[
              'p-4 rounded-lg border flex items-start',
              alert.type === 'warning' ? 'bg-amber-50 border-amber-200' : 'bg-red-50 border-red-200'
            ]"
          >
            <svg
              :class="[
                'w-5 h-5 mr-3 mt-0.5',
                alert.type === 'warning' ? 'text-amber-600' : 'text-red-600'
              ]"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div class="flex-1">
              <p :class="[
                'text-sm font-medium',
                alert.type === 'warning' ? 'text-amber-800' : 'text-red-800'
              ]">
                {{ alert.message }}
              </p>
              <Link :href="alert.link" :class="[
                'text-sm font-medium mt-1 inline-block',
                alert.type === 'warning' ? 'text-amber-700 hover:text-amber-900' : 'text-red-700 hover:text-red-900'
              ]">
                View Details →
              </Link>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>
  
  