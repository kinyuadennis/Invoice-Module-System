<?php

namespace App\Http\Controllers\User;

use App\Config\GatewayConstants;
use App\Config\SubscriptionConstants;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\CurrentCompanyService;
use App\Services\SubscriptionService;
use App\Subscriptions\Repositories\SubscriptionRepository;
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
        private SubscriptionService $subscriptionService,
        private SubscriptionRepository $subscriptionRepository
    ) {}

    /**
     * Initiate a subscription payment.
     *
     * Creates a subscription (PENDING status) and initiates payment with the appropriate gateway.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Validate phone based on user country (KE → M-Pesa requires phone)
        $phoneRule = $user->country === GatewayConstants::COUNTRY_KENYA ? 'required|string' : 'nullable|string';

        $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'phone' => $phoneRule,
            'customer_id' => 'nullable|string', // Optional for Stripe
        ]);
        $companyId = CurrentCompanyService::requireId();

        // Get subscription plan
        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);
        if (! $plan->is_active) {
            return back()->withErrors(['plan' => 'Selected plan is not available']);
        }

        DB::beginTransaction();

        try {
            // Create subscription (PENDING status) via repository (audited)
            $subscription = $this->subscriptionRepository->create([
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

    /**
     * Display a listing of user's subscriptions.
     */
    public function index()
    {
        $user = auth()->user();
        $companyId = CurrentCompanyService::requireId();

        $subscriptions = Subscription::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->with('plan')
            ->latest()
            ->get();

        $availablePlans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->get();

        return view('user.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'availablePlans' => $availablePlans,
        ]);
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        $companyId = CurrentCompanyService::requireId();

        // Ensure subscription belongs to user's company
        if ($subscription->company_id !== $companyId) {
            abort(403, 'Unauthorized');
        }

        // Ensure subscription belongs to authenticated user
        if ($subscription->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        try {
            $this->subscriptionService->cancelSubscription($subscription);

            return back()->with('success', 'Subscription cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Subscription cancellation failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to cancel subscription: '.$e->getMessage()]);
        }
    }
}
