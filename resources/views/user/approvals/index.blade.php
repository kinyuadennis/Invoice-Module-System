@extends('layouts.user')

@section('title', 'Pending Approvals')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Pending Approvals</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Review and approve invoices, estimates, and expenses</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('user.approvals.index', ['type' => 'invoice']) }}" class="px-4 py-2 text-sm font-medium {{ $type === 'invoice' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 dark:text-gray-200' }} rounded-lg hover:bg-gray-200">
                Invoices
            </a>
            <a href="{{ route('user.approvals.index', ['type' => 'estimate']) }}" class="px-4 py-2 text-sm font-medium {{ $type === 'estimate' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 dark:text-gray-200' }} rounded-lg hover:bg-gray-200">
                Estimates
            </a>
            <a href="{{ route('user.approvals.index', ['type' => 'expense']) }}" class="px-4 py-2 text-sm font-medium {{ $type === 'expense' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 dark:text-gray-200' }} rounded-lg hover:bg-gray-200">
                Expenses
            </a>
            <a href="{{ route('user.approvals.index') }}" class="px-4 py-2 text-sm font-medium {{ !$type ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 dark:text-gray-200' }} rounded-lg hover:bg-gray-200">
                All
            </a>
        </div>
    </div>

    @if(count($pendingApprovals) > 0)
        <div class="space-y-4">
            @foreach($pendingApprovals as $approval)
                <x-card>
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <x-badge variant="warning">{{ ucfirst($approval['approvable_type']) }}</x-badge>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $approval['approvable']['number'] ?? 'N/A' }}
                                </h3>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                @if(isset($approval['approvable']['client']))
                                    <div>
                                        <p class="text-xs text-gray-500">Client</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $approval['approvable']['client'] }}</p>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-xs text-gray-500">Amount</p>
                                    <p class="text-sm font-medium text-gray-900">KES {{ number_format($approval['approvable']['amount'] ?? 0, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Requested By</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $approval['requested_by'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Requested At</p>
                                    <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($approval['requested_at'])->format('M d, Y H:i') }}</p>
                                </div>
                            </div>

                            @if(isset($approval['approvable']['description']))
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ $approval['approvable']['description'] }}</p>
                            @endif

                            @if($approval['notes'])
                                <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                                    <p class="text-xs text-gray-500 mb-1">Notes:</p>
                                    <p class="text-sm text-gray-700 dark:text-gray-200">{{ $approval['notes'] }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2 ml-4">
                            @php
                                $viewRoute = match($approval['approvable_type']) {
                                    'Invoice' => route('user.invoices.show', $approval['approvable_id']),
                                    'Estimate' => route('user.estimates.show', $approval['approvable_id']),
                                    'Expense' => route('user.expenses.show', $approval['approvable_id']),
                                    default => '#',
                                };
                            @endphp
                            <a href="{{ $viewRoute }}" class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 text-center">
                                View
                            </a>
                            <button 
                                onclick="approveApproval({{ $approval['id'] }})"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                            >
                                Approve
                            </button>
                            <button 
                                onclick="rejectApproval({{ $approval['id'] }})"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                            >
                                Reject
                            </button>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @else
        <x-card>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No pending approvals</h3>
                <p class="mt-1 text-sm text-gray-500">All items have been reviewed.</p>
            </div>
        </x-card>
    @endif
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" onclick="if(event.target === this) closeRejectModal()">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="reject-form" onsubmit="submitReject(event)">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Approval Request</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Rejection Reason *</label>
                        <textarea 
                            name="rejection_reason"
                            rows="4"
                            required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            placeholder="Please provide a reason for rejection..."
                        ></textarea>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Additional Notes</label>
                        <textarea 
                            name="notes"
                            rows="2"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        ></textarea>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Reject
                    </button>
                    <button type="button" onclick="closeRejectModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentApprovalId = null;

function approveApproval(id) {
    if (!confirm('Approve this request?')) return;
    
    fetch(`/app/approvals/${id}/approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Approval request approved successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to approve'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to approve. Please try again.');
    });
}

function rejectApproval(id) {
    currentApprovalId = id;
    document.getElementById('reject-modal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
    document.getElementById('reject-form').reset();
    currentApprovalId = null;
}

function submitReject(event) {
    event.preventDefault();
    
    if (!currentApprovalId) return;
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch(`/app/approvals/${currentApprovalId}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Approval request rejected successfully!');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to reject'));
            if (data.errors) {
                console.error('Validation errors:', data.errors);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to reject. Please try again.');
    });
}
</script>
@endpush
@endsection


