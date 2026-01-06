@extends('layouts.user')

@section('title', $recurringInvoice->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $recurringInvoice->name }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $recurringInvoice->description }}</p>
        </div>
        <div class="flex gap-2">
            @if($recurringInvoice->status === 'active')
                <form method="POST" action="{{ route('user.recurring-invoices.pause', $recurringInvoice) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200">Pause</button>
                </form>
            @elseif($recurringInvoice->status === 'paused')
                <form method="POST" action="{{ route('user.recurring-invoices.resume', $recurringInvoice) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-100 text-green-800 rounded-md hover:bg-green-200">Resume</button>
                </form>
            @endif
            @if($recurringInvoice->status !== 'cancelled')
                <form method="POST" action="{{ route('user.recurring-invoices.cancel', $recurringInvoice) }}" onsubmit="return confirm('Are you sure?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-100 text-red-800 rounded-md hover:bg-red-200">Cancel</button>
                </form>
            @endif
            <a href="{{ route('user.recurring-invoices.edit', $recurringInvoice) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Edit</a>
            <a href="{{ route('user.recurring-invoices.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status & Schedule -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Schedule Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <x-badge :variant="match($recurringInvoice->status) {
                            'active' => 'success',
                            'paused' => 'warning',
                            'cancelled' => 'danger',
                            'completed' => 'info',
                            default => 'default'
                        }">{{ ucfirst($recurringInvoice->status) }}</x-badge>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Frequency</p>
                        <p class="text-sm font-medium text-gray-900">Every {{ $recurringInvoice->interval }} {{ str($recurringInvoice->frequency)->plural() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Start Date</p>
                        <p class="text-sm font-medium text-gray-900">{{ $recurringInvoice->start_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Next Run Date</p>
                        <p class="text-sm font-medium text-gray-900">{{ $recurringInvoice->next_run_date->format('M d, Y') }}</p>
                    </div>
                    @if($recurringInvoice->end_date)
                    <div>
                        <p class="text-sm text-gray-500">End Date</p>
                        <p class="text-sm font-medium text-gray-900">{{ $recurringInvoice->end_date->format('M d, Y') }}</p>
                    </div>
                    @endif
                    @if($recurringInvoice->max_occurrences)
                    <div>
                        <p class="text-sm text-gray-500">Max Occurrences</p>
                        <p class="text-sm font-medium text-gray-900">{{ $recurringInvoice->max_occurrences }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">Total Generated</p>
                        <p class="text-sm font-medium text-gray-900">{{ $recurringInvoice->total_generated }} invoice(s)</p>
                    </div>
                    @if($recurringInvoice->last_generated_at)
                    <div>
                        <p class="text-sm text-gray-500">Last Generated</p>
                        <p class="text-sm font-medium text-gray-900">{{ $recurringInvoice->last_generated_at->format('M d, Y') }}</p>
                    </div>
                    @endif
                </div>
            </x-card>

            <!-- Client Information -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Client Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Client Name</p>
                        <p class="text-sm font-medium text-gray-900">{{ $recurringInvoice->client->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="text-sm font-medium text-gray-900">{{ $recurringInvoice->client->email ?? 'N/A' }}</p>
                    </div>
                </div>
            </x-card>

            <!-- Invoice Template Preview -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Template</h2>
                @if(isset($recurringInvoice->invoice_data['line_items']))
                    <div class="space-y-2 mb-4">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200">Line Items</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tax Rate</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recurringInvoice->invoice_data['line_items'] as $item)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $item['description'] ?? '' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $item['quantity'] ?? 1 }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $item['tax_rate'] ?? 0 }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if(isset($recurringInvoice->invoice_data['notes']))
                    <div class="mb-4">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Notes</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $recurringInvoice->invoice_data['notes'] }}</p>
                    </div>
                @endif

                @if(isset($recurringInvoice->invoice_data['terms_and_conditions']))
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Terms & Conditions</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $recurringInvoice->invoice_data['terms_and_conditions'] }}</p>
                    </div>
                @endif
            </x-card>

            <!-- Generated Invoices -->
            @if($recurringInvoice->generatedInvoices->count() > 0)
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Generated Invoices</h2>
                <div class="space-y-2">
                    @foreach($recurringInvoice->generatedInvoices->take(10) as $invoice)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <a href="{{ route('user.invoices.show', $invoice) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                    {{ $invoice->invoice_number ?? $invoice->invoice_reference }}
                                </a>
                                <p class="text-xs text-gray-500">{{ $invoice->issue_date->format('M d, Y') }}</p>
                            </div>
                            <x-badge :variant="match($invoice->status) {
                                'draft' => 'default',
                                'sent' => 'info',
                                'paid' => 'success',
                                'overdue' => 'danger',
                                default => 'default'
                            }">{{ ucfirst($invoice->status) }}</x-badge>
                        </div>
                    @endforeach
                    @if($recurringInvoice->generatedInvoices->count() > 10)
                        <p class="text-sm text-gray-500 text-center">... and {{ $recurringInvoice->generatedInvoices->count() - 10 }} more</p>
                    @endif
                </div>
            </x-card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Settings -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Settings</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Auto-send</span>
                        <x-badge :variant="$recurringInvoice->auto_send ? 'success' : 'default'">
                            {{ $recurringInvoice->auto_send ? 'Enabled' : 'Disabled' }}
                        </x-badge>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Send Reminders</span>
                        <x-badge :variant="$recurringInvoice->send_reminders ? 'success' : 'default'">
                            {{ $recurringInvoice->send_reminders ? 'Enabled' : 'Disabled' }}
                        </x-badge>
                    </div>
                </div>
            </x-card>

            <!-- Actions -->
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                <div class="space-y-2">
                    <form method="POST" action="{{ route('user.recurring-invoices.generate', $recurringInvoice) }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Generate Invoice Now
                        </button>
                    </form>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

