@extends('layouts.user')

@section('title', 'Pending Approvals')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Pending Approvals</h1>
        <p class="mt-1 text-sm font-medium text-gray-500 dark:text-[#9A9A9A]">Review and approve invoices, estimates, and expenses</p>
    </div>

    <!-- Stats/Context -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-[#1E1E1E] p-6 rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm">
            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Total Pending</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">{{ $approvals->count() ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] p-6 rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm">
            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Invoices Value</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">KES {{ number_format($approvals->where('type', 'invoice')->sum('amount') ?? 0, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1E1E1E] p-6 rounded-2xl border border-gray-100 dark:border-[#2A2A2A] shadow-sm">
            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Expenses Value</p>
            <p class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">KES {{ number_format($approvals->where('type', 'expense')->sum('amount') ?? 0, 2) }}</p>
        </div>
    </div>

    <!-- Approval Type Tabs -->
    <div class="flex gap-4 mb-8 border-b border-gray-100 dark:border-[#2A2A2A] overflow-x-auto pb-1">
        <a href="{{ route('user.approvals.index', ['type' => 'invoice']) }}"
            class="px-1 py-4 text-xs font-black uppercase tracking-widest border-b-2 {{ $type === 'invoice' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-[#9A9A9A] dark:hover:text-white' }} transition-all duration-200 whitespace-nowrap">
            Invoices
        </a>
        <a href="{{ route('user.approvals.index', ['type' => 'estimate']) }}"
            class="px-1 py-4 text-xs font-black uppercase tracking-widest border-b-2 {{ $type === 'estimate' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-[#9A9A9A] dark:hover:text-white' }} transition-all duration-200 whitespace-nowrap">
            Estimates
        </a>
        <a href="{{ route('user.approvals.index', ['type' => 'expense']) }}"
            class="px-1 py-4 text-xs font-black uppercase tracking-widest border-b-2 {{ $type === 'expense' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-[#9A9A9A] dark:hover:text-white' }} transition-all duration-200 whitespace-nowrap">
            Expenses
        </a>
        <a href="{{ route('user.approvals.index') }}"
            class="px-1 py-4 text-xs font-black uppercase tracking-widest border-b-2 {{ !$type ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-[#9A9A9A] dark:hover:text-white' }} transition-all duration-200 whitespace-nowrap">
            All
        </a>
    </div>

    @if(isset($pendingApprovals) && count($pendingApprovals) > 0)
    <!-- Approval Items -->
    <div class="space-y-4">
        @forelse($pendingApprovals ?? [] as $approval)
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400">
                            Requires Approval
                        </span>
                        <span class="text-xs font-medium text-gray-500 dark:text-[#9A9A9A]">
                            {{ \Carbon\Carbon::parse($approval['requested_at'])->diffForHumans() }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Reference</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $approval['approvable']['number'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Entity</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $approval['approvable']['client'] ?? 'N/A' }}</p>
                        </div>
                        @if(isset($approval['approvable']['amount']))
                        <div>
                            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Amount</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">KES {{ number_format($approval['approvable']['amount'] ?? 0, 2) }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Submitted By</p>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ $approval['requested_by'] ?? 'Unknown' }}</p>
                        </div>
                    </div>

                    @if(isset($approval['notes']) && $approval['notes'])
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-black/20 rounded-xl border border-gray-100 dark:border-[#2A2A2A]">
                        <p class="text-[10px] font-black text-gray-400 dark:text-[#9A9A9A] uppercase tracking-widest mb-1">Notes</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 italic">"{{ $approval['notes'] }}"</p>
                    </div>
                    @endif
                </div>

                <div class="flex flex-col gap-3 min-w-[140px]">
                    @php
                    $viewRoute = match($approval['approvable_type']) {
                    'Invoice' => route('user.invoices.show', $approval['approvable_id']),
                    'Estimate' => route('user.estimates.show', $approval['approvable_id']),
                    'Expense' => route('user.expenses.show', $approval['approvable_id']),
                    default => '#',
                    };
                    @endphp
                    <a href="{{ $viewRoute }}" class="inline-flex items-center justify-center px-4 py-2 border border-blue-200 dark:border-blue-500/30 text-xs font-black rounded-xl text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 uppercase tracking-widest transition-colors w-full">
                        View Details
                    </a>

                    <button
                        onclick="approveApproval({{ $approval['id'] }})"
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-xs font-black rounded-xl text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 uppercase tracking-widest w-full transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Approve
                    </button>

                    <button
                        onclick="openRejectModal('{{ $approval['approvable_type'] }}', {{ $approval['id'] }})"
                        class="inline-flex items-center justify-center px-4 py-2 border border-red-200 dark:border-red-500/30 text-xs font-black rounded-xl text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 uppercase tracking-widest w-full transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reject
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#2A2A2A] p-12 text-center shadow-sm">
            <div class="flex justify-center mb-4">
                <div class="p-4 bg-green-100 dark:bg-green-500/10 rounded-full">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <h3 class="mt-2 text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">All caught up!</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-[#9A9A9A]">No pending approvals at this time.</p>
        </div>
        @endforelse
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
<div id="reject-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeRejectModal()"></div>

        <!-- Modal panel -->
        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
        <div class="inline-block transform overflow-hidden rounded-2xl bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#2A2A2A] text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
            <form id="reject-form" method="POST">
                @csrf
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/10 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-black leading-6 text-gray-900 dark:text-white tracking-tight" id="modal-title">Reject Approval</h3>
                            <div class="mt-4">
                                <label for="reason" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                                    Rejection Reason
                                </label>
                                <textarea
                                    name="reason"
                                    id="reason"
                                    rows="3"
                                    class="block w-full rounded-xl border-gray-200 dark:border-[#333333] dark:bg-black/50 dark:text-white focus:border-red-500 focus:ring-red-500 sm:text-sm p-3"
                                    placeholder="Please provide a reason for rejection..."
                                    required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-[#111111] px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto uppercase tracking-wider transition-colors">
                        Confirm Rejection
                    </button>
                    <button type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white dark:bg-[#222222] px-4 py-2.5 text-sm font-bold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-[#333333] hover:bg-gray-50 dark:hover:bg-[#1E1E1E] sm:mt-0 sm:w-auto uppercase tracking-wider transition-colors" onclick="closeRejectModal()">
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