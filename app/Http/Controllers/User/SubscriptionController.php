<?php

namespace App\Http\Controllers\User;

use App\Config\SubscriptionConstants;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\CurrentCompanyService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Subscription Controller
 *
 * Handles subscription initiation and management for authenticated users.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Initiate a subscription payment.
     *
     * Creates a subscription (PENDING status) and initiates payment with the appropriate gateway.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'phone' => 'required_if:gateway,mpesa|string', // Required for M-Pesa
            'customer_id' => 'nullable|string', // Optional for Stripe
        ]);

        $user = $request->user();
        $companyId = CurrentCompanyService::requireId();

        // Get subscription plan
        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);
        if (! $plan->is_active) {
            return back()->withErrors(['plan' => 'Selected plan is not available']);
        }

        DB::beginTransaction();

        try {
            // Create subscription (PENDING status)
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'company_id' => $companyId,
                'subscription_plan_id' => $plan->id,
                'plan_code' => $plan->slug ?? $plan->name,
                'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_PENDING,
                'starts_at' => now(),
                'auto_renew' => true,
            ]);

            // Prepare user details for gateway
            $userDetails = [
                'phone' => $request->phone ?? null,
                'customerId' => $request->customer_id ?? null,
                'country' => $user->country ?? null,
            ];

            // Initiate payment
            $result = $this->subscriptionService->initiateSubscriptionPayment($subscription, $userDetails);

            if (! $result['success']) {
                throw new \Exception($result['error'] ?? 'Payment initiation failed');
            }

            DB::commit();

            // Return response based on gateway (client_secret for Stripe, transaction_id for M-Pesa)
            return response()->json([
                'success' => true,
                'subscription_id' => $subscription->id,
                'client_secret' => $result['client_secret'] ?? null,
                'transaction_id' => $result['transaction_id'] ?? null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Subscription initiation failed', [
                'user_id' => $user->id,
                'company_id' => $companyId,
                'plan_id' => $request->subscription_plan_id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to initiate subscription: '.$e->getMessage()]);
        }
    }
}
