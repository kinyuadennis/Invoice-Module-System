@props([
    'paymentId',
    'polling' => true,
    'pollInterval' => 5000,
    'maxPolls' => 60,
])

<div 
    x-data="paymentStatusPollingComponent({{ $paymentId }}, {{ $polling ? 'true' : 'false' }}, {{ $pollInterval }}, {{ $maxPolls }})"
    x-init="init()"
    {{ $attributes }}
>
    <!-- Status Display -->
    <div class="flex items-center gap-3">
        <x-payments.payment-status-badge 
            :status="$status ?? 'pending'" 
            size="md"
            x-show="status"
        />
        
        <!-- Polling Indicator -->
        <div x-show="polling && !isTerminal" x-cloak class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-400">
            <x-shared.loading-spinner size="sm" />
            <span>Checking...</span>
        </div>
    </div>

    <!-- Status Message -->
    <p x-show="statusMessage" x-cloak class="mt-2 text-sm text-gray-600 dark:text-gray-300 dark:text-gray-400" x-text="statusMessage"></p>
</div>

@push('scripts')
<script>
function paymentStatusPollingComponent(paymentId, shouldPoll, pollInterval, maxPolls) {
    return {
        paymentId: paymentId,
        status: null,
        polling: shouldPoll,
        pollCount: 0,
        maxPolls: maxPolls,
        pollIntervalMs: pollInterval,
        intervalId: null,
        isTerminal: false,
        statusMessage: '',

        async init() {
            // Initial status check
            await this.checkStatus();
            
            if (this.polling && !this.isTerminal) {
                this.startPolling();
            }
        },

        startPolling() {
            this.intervalId = setInterval(() => {
                this.checkStatus();
            }, this.pollIntervalMs);
        },

        stopPolling() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
            this.polling = false;
        },

        async checkStatus() {
            if (this.pollCount >= this.maxPolls) {
                this.stopPolling();
                return;
            }

            try {
                const response = await fetch(`/user/api/subscriptions/payment-status/${this.paymentId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Failed to check payment status');
                }

                const data = await response.json();
                this.status = data.status.toLowerCase();
                this.isTerminal = data.is_terminal;
                this.pollCount++;

                // Update status message
                this.updateStatusMessage();

                // Stop polling if terminal
                if (this.isTerminal) {
                    this.stopPolling();
                }
            } catch (error) {
                console.error('Payment status polling error:', error);
            }
        },

        updateStatusMessage() {
            const messages = {
                'success': 'Payment confirmed',
                'pending': 'Waiting for confirmation',
                'initiated': 'Payment initiated',
                'failed': 'Payment failed',
                'timeout': 'Status check timeout',
            };
            this.statusMessage = messages[this.status] || '';
        },
    };
}
</script>
@endpush

