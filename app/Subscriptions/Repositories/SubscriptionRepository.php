<?php

namespace App\Subscriptions\Repositories;

use App\Models\AuditLog;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

/**
 * Subscription Repository
 *
 * Provides audited CRUD operations for subscriptions.
 * All changes are logged for audit trail.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class SubscriptionRepository
{
    /**
     * Create a new subscription with audit log.
     */
    public function create(array $data): Subscription
    {
        $subscription = Subscription::create($data);

        $this->logChange($subscription, 'created', [
            'status' => $subscription->status,
            'plan_code' => $subscription->plan_code,
            'gateway' => $subscription->gateway,
        ]);

        return $subscription;
    }

    /**
     * Update a subscription with audit log.
     */
    public function update(Subscription $subscription, array $data): Subscription
    {
        // Store original values before update
        $original = [];
        foreach ($data as $key => $value) {
            if ($subscription->isFillable($key)) {
                $original[$key] = $subscription->getOriginal($key);
            }
        }

        // Update the subscription
        $subscription->update($data);

        // Track changes by comparing original values with new values
        $changes = [];
        foreach ($data as $key => $value) {
            if (isset($original[$key]) && $original[$key] != $value) {
                $changes[$key] = [
                    'from' => $original[$key],
                    'to' => $value,
                ];
            }
        }

        if (! empty($changes)) {
            $this->logChange($subscription, 'updated', $changes);
        }

        return $subscription;
    }

    /**
     * Find a subscription by ID.
     */
    public function find(int $id): ?Subscription
    {
        return Subscription::find($id);
    }

    /**
     * Find active subscription for a user.
     */
    public function findActiveForUser(int $userId): ?Subscription
    {
        return Subscription::where('user_id', $userId)
            ->where('status', \App\Config\SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE)
            ->first();
    }

    /**
     * Log a change to the subscription for audit trail.
     */
    private function logChange(Subscription $subscription, string $action, array $details): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => "subscription_{$action}",
                'model_type' => Subscription::class,
                'model_id' => $subscription->id,
                'changes' => $details,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Log to application log if audit log fails (don't break the flow)
            Log::warning('Failed to create audit log for subscription', [
                'subscription_id' => $subscription->id,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
