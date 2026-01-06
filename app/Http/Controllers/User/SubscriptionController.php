<?php

namespace App\Http\Controllers\User;

use App\Config\GatewayConstants;
use App\Config\PaymentConstants;
use App\Config\SubscriptionConstants;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Payments\Events\PaymentConfirmed;
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
            'payment_method' => 'nullable|string', // Stripe payment method ID
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
            // Determine gateway based on user country (must be set at creation per blueprint invariant)
            $gateway = $user->country === GatewayConstants::COUNTRY_KENYA ? PaymentConstants::GATEWAY_MPESA : PaymentConstants::GATEWAY_STRIPE;

            // Create subscription (PENDING status) via repository (audited)
            // Gateway must be set at creation as it's immutable per blueprint invariant
            // For free plans, use GATEWAY_STRIPE as a consistent placeholder (matches AuthController)
            $subscriptionGateway = ($plan->price == 0 || $plan->price === null)
                ? PaymentConstants::GATEWAY_STRIPE
                : $gateway;

            // Per blueprint: New subscriptions start as 'free' status
            // They transition to 'active' when payment is confirmed
            $subscription = $this->subscriptionRepository->create([
                'user_id' => $user->id,
                'company_id' => $companyId,
                'subscription_plan_id' => $plan->id,
                'plan_code' => $plan->slug ?? $plan->name,
                'status' => SubscriptionConstants::SUBSCRIPTION_STATUS_FREE,
                'gateway' => $subscriptionGateway, // Consistent gateway value for free plans
                'starts_at' => now(),
                'auto_renew' => true,
            ]);

            // Handle free plans differently - activate immediately without payment
            if ($plan->price == 0 || $plan->price === null) {
                // Create a $0 payment record FIRST (required by transitionToActive invariant)
                // Use GATEWAY_STRIPE for consistency with AuthController and subscription gateway
                $payment = Payment::create([
                    'company_id' => $companyId,
                    'payable_type' => Subscription::class,
                    'payable_id' => $subscription->id,
                    'amount' => 0,
                    'gateway' => PaymentConstants::GATEWAY_STRIPE, // Consistent with subscription gateway
                    'status' => PaymentConstants::PAYMENT_STATUS_SUCCESS,
                    'payment_date' => now(),
                    'paid_at' => now(),
                    'gateway_transaction_id' => 'FREE-'.strtoupper(\Illuminate\Support\Str::uuid()),
                    'gateway_metadata' => [
                        'type' => 'free_plan_activation',
                        'plan_code' => $plan->slug ?? $plan->name,
                        'note' => 'Free plan activation - no payment required',
                    ],
                ]);

                // Now transition to active (payment exists, so invariant is satisfied)
                $subscription->transitionToActive();

                // Set subscription end date (free plans don't expire, but set a far future date)
                $subscription->update([
                    'ends_at' => null, // Free plan doesn't expire
                    'next_billing_at' => null, // No billing for free plan
                ]);

                // Emit PaymentConfirmed event to trigger invoice creation
                event(new PaymentConfirmed($payment));

                DB::commit();

                return response()->json([
                    'success' => true,
                    'subscription_id' => $subscription->id,
                    'payment_id' => $payment->id,
                    'message' => 'Free plan activated successfully',
                ]);
            }

            // For paid plans, proceed with payment initiation

            // Handle Stripe subscriptions with Cashier
            if ($gateway === PaymentConstants::GATEWAY_STRIPE && $request->payment_method) {
                return $this->handleStripeSubscription($user, $subscription, $plan, $request->payment_method);
            }

            // For M-Pesa or Stripe without payment method yet, use service
            // Prepare user details for gateway
            $userDetails = [
                'phone' => $request->phone ?? null,
                'customerId' => $request->customer_id ?? null,
                'country' => $user->country ?? null,
                'payment_method' => $request->payment_method ?? null,
            ];

            // Initiate payment
            $result = $this->subscriptionService->initiateSubscriptionPayment($subscription, $userDetails);

            if (! $result['success']) {
                throw new \Exception($result['error'] ?? 'Payment initiation failed');
            }

            DB::commit();

            // Get the payment attempt ID from the result (per blueprint: use payment_attempts)
            $paymentAttemptId = $result['payment_attempt_id'] ?? null;

            // Return response based on gateway (client_secret for Stripe, transaction_id for M-Pesa)
            return response()->json([
                'success' => true,
                'subscription_id' => $subscription->id,
                'payment_attempt_id' => $paymentAttemptId,
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
     * Handle Stripe subscription creation using Cashier.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleStripeSubscription(User $user, Subscription $subscription, SubscriptionPlan $plan, string $paymentMethodId)
    {
        try {
            // Get Stripe price ID from plan (should be stored in plan metadata)
            // For now, use plan slug - in production, store Stripe price ID in subscription_plans table
            $stripePriceId = $plan->stripe_price_id ?? $plan->slug;

            // Create subscription using Cashier
            $cashierSubscription = $user->newSubscription('default', $stripePriceId)
                ->create($paymentMethodId);

            // Update our subscription record with Stripe details
            // Note: gateway is already set at creation, but ensure it's correct
            $subscription->update([
                'stripe_id' => $cashierSubscription->stripe_id,
                'gateway_subscription_id' => $cashierSubscription->stripe_id, // Per blueprint: generic gateway ID
                'stripe_status' => $cashierSubscription->stripe_status,
                'stripe_price' => $stripePriceId,
                'type' => 'default',
                'payment_method' => 'card',
                'payment_reference' => $cashierSubscription->stripe_id,
            ]);

            // Per blueprint: If subscription is immediately active, create Payment record (succeeded)
            // If not active yet, create PaymentAttempt (will be confirmed via webhook)
            if ($cashierSubscription->stripe_status === 'active') {
                // Subscription is active - create Payment record (succeeded)
                $payment = Payment::create([
                    'company_id' => $subscription->company_id,
                    'payable_type' => Subscription::class,
                    'payable_id' => $subscription->id,
                    'amount' => $plan->price,
                    'gateway' => PaymentConstants::GATEWAY_STRIPE,
                    'status' => PaymentConstants::PAYMENT_STATUS_SUCCESS,
                    'gateway_transaction_id' => $cashierSubscription->stripe_id,
                    'payment_date' => now(),
                    'paid_at' => now(),
                    'gateway_metadata' => [
                        'stripe_subscription_id' => $cashierSubscription->stripe_id,
                        'stripe_price_id' => $stripePriceId,
                    ],
                ]);

                // Update subscription with period dates
                $subscription->update([
                    'current_period_start' => $cashierSubscription->created,
                    'current_period_end' => $cashierSubscription->current_period_end,
                ]);

                // Payment exists now, so invariant is satisfied
                $subscription->transitionToActive();
                $subscription->update([
                    'starts_at' => now(),
                    'ends_at' => $cashierSubscription->ends_at,
                ]);

                // Emit PaymentConfirmed event
                event(new \App\Payments\Events\PaymentConfirmed($payment));
            } else {
                // Subscription not active yet - create PaymentAttempt (will be confirmed via webhook)
                $idempotencyKey = (string) \Illuminate\Support\Str::uuid();
                $lastAttempt = $subscription->paymentAttempts()
                    ->orderBy('attempt_number', 'desc')
                    ->first();
                $attemptNumber = $lastAttempt ? $lastAttempt->attempt_number + 1 : 1;

                // Per blueprint: Payment attempts start as INITIATED, then transition to PENDING when gateway accepts
                $paymentAttempt = PaymentAttempt::create([
                    'subscription_id' => $subscription->id,
                    'amount' => $plan->price,
                    'currency' => $subscription->company?->currency ?? 'KES',
                    'gateway' => PaymentConstants::GATEWAY_STRIPE,
                    'attempt_number' => $attemptNumber,
                    'status' => PaymentAttempt::STATUS_INITIATED, // Start as initiated, webhook will transition to pending/succeeded
                    'gateway_transaction_id' => $cashierSubscription->stripe_id,
                    'idempotency_key' => $idempotencyKey,
                    'initiated_at' => now(),
                    'gateway_metadata' => [
                        'stripe_subscription_id' => $cashierSubscription->stripe_id,
                        'stripe_price_id' => $stripePriceId,
                    ],
                ]);

                $payment = null; // No payment record yet - will be created on webhook confirmation
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment?->id,
                'payment_attempt_id' => $paymentAttempt->id ?? null,
                'stripe_subscription_id' => $cashierSubscription->stripe_id,
                'status' => $cashierSubscription->stripe_status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stripe subscription creation failed', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Display a listing of user's subscriptions.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = CurrentCompanyService::requireId();

        $subscriptions = Subscription::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->with(['plan', 'payments' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->latest()
            ->get();

        $availablePlans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->get();

        // Get recent payments for subscriptions
        $recentPayments = Payment::where('company_id', $companyId)
            ->where('payable_type', Subscription::class)
            ->with(['payable.plan'])
            ->latest()
            ->limit(10)
            ->get();

        return view('user.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'availablePlans' => $availablePlans,
            'recentPayments' => $recentPayments,
        ]);
    }

    /**
     * Show checkout page for subscription.
     */
    public function checkout(Request $request)
    {
        $user = $request->user();
        $companyId = CurrentCompanyService::requireId();

        // Get selected plan ID from query parameter
        $planId = $request->query('plan');

        // Get available plans
        $availablePlans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->get();

        // Check if no plans are available
        if ($availablePlans->isEmpty()) {
            return redirect()->route('user.subscriptions.index')
                ->withErrors(['error' => 'No subscription plans are currently available. Please contact support.']);
        }

        // Get selected plan if provided
        $selectedPlan = $planId ? SubscriptionPlan::find($planId) : null;

        // Check if user already has an active subscription
        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('status', SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE)
            ->with('plan')
            ->first();

        // Determine suggested gateway based on user country
        $suggestedGateway = $user->country === GatewayConstants::COUNTRY_KENYA
            ? PaymentConstants::GATEWAY_MPESA
            : PaymentConstants::GATEWAY_STRIPE;

        return view('user.subscriptions.checkout', [
            'availablePlans' => $availablePlans,
            'selectedPlan' => $selectedPlan,
            'activeSubscription' => $activeSubscription,
            'suggestedGateway' => $suggestedGateway,
            'userCountry' => $user->country,
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

    /**
     * Display payment status page with polling.
     * Per blueprint: Status pages poll payment_attempt (gateway-agnostic).
     * Accepts both Payment (for succeeded) and PaymentAttempt (for pending).
     *
     * Route model binding: Tries Payment first, then PaymentAttempt.
     */
    public function paymentStatus(Request $request, $paymentOrAttempt)
    {
        $user = $request->user();
        $companyId = CurrentCompanyService::requireId();

        // Resolve paymentOrAttempt if it's an integer ID (fallback if route binding fails)
        if (is_numeric($paymentOrAttempt) && ! ($paymentOrAttempt instanceof Payment) && ! ($paymentOrAttempt instanceof PaymentAttempt)) {
            $paymentOrAttempt = Payment::find($paymentOrAttempt) ?? PaymentAttempt::find($paymentOrAttempt);
            if (! $paymentOrAttempt) {
                abort(404, 'Payment or payment attempt not found');
            }
        }

        // Determine if we have a Payment or PaymentAttempt
        $payment = null;
        $paymentAttempt = null;
        $subscription = null;

        if ($paymentOrAttempt instanceof Payment) {
            $payment = $paymentOrAttempt;

            // Ensure payment belongs to user's company
            if ($payment->company_id !== $companyId) {
                abort(403, 'Unauthorized');
            }

            // Ensure payment is for a subscription
            if ($payment->payable_type !== Subscription::class) {
                abort(404, 'Payment not found');
            }

            $subscription = $payment->payable;

            // Find related payment attempt if exists
            if ($payment->gateway_transaction_id) {
                $paymentAttempt = PaymentAttempt::where('gateway_transaction_id', $payment->gateway_transaction_id)
                    ->where('subscription_id', $subscription->id)
                    ->first();
            }
        } elseif ($paymentOrAttempt instanceof PaymentAttempt) {
            $paymentAttempt = $paymentOrAttempt;
            $subscription = $paymentAttempt->subscription;

            // Ensure subscription belongs to user's company
            if ($subscription->company_id !== $companyId) {
                abort(403, 'Unauthorized');
            }

            // Find related payment if attempt succeeded
            if ($paymentAttempt->status === PaymentAttempt::STATUS_SUCCEEDED) {
                $payment = Payment::where('gateway_transaction_id', $paymentAttempt->gateway_transaction_id)
                    ->where('payable_type', Subscription::class)
                    ->where('payable_id', $subscription->id)
                    ->first();
            }
        } else {
            abort(404, 'Payment or payment attempt not found');
        }

        // Handle edge case: subscription was deleted
        if (! $subscription) {
            return redirect()->route('user.subscriptions.index')
                ->with('error', 'The subscription associated with this payment no longer exists.');
        }

        // Ensure subscription belongs to user
        if ($subscription->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $plan = $subscription->plan;

        // Handle edge case: plan was deleted
        if (! $plan) {
            return redirect()->route('user.subscriptions.index')
                ->with('error', 'The subscription plan no longer exists.');
        }

        // Per blueprint: Poll if attempt is not in terminal state
        $needsPolling = $paymentAttempt
            && ! $paymentAttempt->isTerminal()
            && (
                $paymentAttempt->gateway === PaymentConstants::GATEWAY_MPESA
                || ($paymentAttempt->gateway === PaymentConstants::GATEWAY_STRIPE
                    && $subscription->status !== SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE)
            );

        return view('user.subscriptions.payment-status', [
            'payment' => $payment,
            'paymentAttempt' => $paymentAttempt,
            'subscription' => $subscription,
            'plan' => $plan,
            'needsPolling' => $needsPolling,
        ]);
    }

    /**
     * API endpoint for polling payment status.
     * Per blueprint: Returns state from payment_attempt (gateway-agnostic).
     */
    public function getPaymentStatus(Request $request, $paymentOrAttempt)
    {
        $user = $request->user();
        $companyId = CurrentCompanyService::requireId();

        // Resolve paymentOrAttempt if it's an integer ID (fallback if route binding fails)
        if (is_numeric($paymentOrAttempt) && ! ($paymentOrAttempt instanceof Payment) && ! ($paymentOrAttempt instanceof PaymentAttempt)) {
            $paymentOrAttempt = Payment::find($paymentOrAttempt) ?? PaymentAttempt::find($paymentOrAttempt);
            if (! $paymentOrAttempt) {
                return response()->json(['error' => 'Payment or payment attempt not found'], 404);
            }
        }

        $paymentAttempt = null;
        $payment = null;
        $subscription = null;

        // Determine if we have a Payment or PaymentAttempt
        if ($paymentOrAttempt instanceof Payment) {
            $payment = $paymentOrAttempt;

            // Ensure payment belongs to user's company
            if ($payment->company_id !== $companyId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($payment->payable_type !== Subscription::class) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            $subscription = $payment->payable;

            // Find related payment attempt
            if ($payment->gateway_transaction_id) {
                $paymentAttempt = PaymentAttempt::where('gateway_transaction_id', $payment->gateway_transaction_id)
                    ->where('subscription_id', $subscription->id)
                    ->first();
            }
        } elseif ($paymentOrAttempt instanceof PaymentAttempt) {
            $paymentAttempt = $paymentOrAttempt;
            $subscription = $paymentAttempt->subscription;

            // Ensure subscription belongs to user's company
            if ($subscription->company_id !== $companyId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Find related payment if attempt succeeded
            if ($paymentAttempt->status === PaymentAttempt::STATUS_SUCCEEDED) {
                $payment = Payment::where('gateway_transaction_id', $paymentAttempt->gateway_transaction_id)
                    ->where('payable_type', Subscription::class)
                    ->where('payable_id', $subscription->id)
                    ->first();
            }
        } else {
            return response()->json(['error' => 'Payment or payment attempt not found'], 404);
        }

        // Handle edge case: subscription was deleted
        if (! $subscription) {
            return response()->json([
                'error' => 'Subscription not found',
                'status' => 'error',
                'is_terminal' => true,
            ], 404);
        }

        // Ensure subscription belongs to user
        if ($subscription->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Refresh from database
        $paymentAttempt?->refresh();
        $payment?->refresh();
        $subscription->refresh();

        // Per blueprint: Return state from payment_attempt
        if ($paymentAttempt) {
            // Map PaymentAttempt status to PaymentConstants status for backward compatibility
            $statusMap = [
                PaymentAttempt::STATUS_INITIATED => PaymentConstants::PAYMENT_STATUS_INITIATED,
                PaymentAttempt::STATUS_PENDING => PaymentConstants::PAYMENT_STATUS_INITIATED,
                PaymentAttempt::STATUS_SUCCEEDED => PaymentConstants::PAYMENT_STATUS_SUCCESS,
                PaymentAttempt::STATUS_FAILED => PaymentConstants::PAYMENT_STATUS_FAILED,
                PaymentAttempt::STATUS_TIMED_OUT => PaymentConstants::PAYMENT_STATUS_TIMEOUT,
            ];

            return response()->json([
                'status' => $statusMap[$paymentAttempt->status] ?? $paymentAttempt->status,
                'gateway' => $paymentAttempt->gateway,
                'gateway_transaction_id' => $paymentAttempt->gateway_transaction_id,
                'amount' => $paymentAttempt->amount,
                'currency' => $paymentAttempt->currency,
                'is_terminal' => $paymentAttempt->isTerminal(),
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'stripe_subscription_id' => $subscription->stripe_id,
                'paid_at' => $payment?->paid_at?->toIso8601String(),
                'error' => $paymentAttempt->error_message,
            ]);
        }

        // Fallback: If we only have a Payment (succeeded), return that
        if ($payment) {
            return response()->json([
                'status' => $payment->status,
                'gateway' => $payment->gateway,
                'gateway_transaction_id' => $payment->gateway_transaction_id,
                'amount' => $payment->amount,
                'currency' => $subscription->company?->currency ?? 'KES',
                'is_terminal' => true, // Payment records are always terminal (succeeded)
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'stripe_subscription_id' => $subscription->stripe_id,
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'error' => null,
            ]);
        }

        return response()->json(['error' => 'Payment attempt not found'], 404);
    }

    /**
     * Display success confirmation page after successful payment.
     */
    public function success(Request $request)
    {
        $user = $request->user();
        $companyId = CurrentCompanyService::requireId();

        // Get payment ID from query parameter
        $paymentId = $request->query('payment');
        $subscriptionId = $request->query('subscription');

        $payment = null;
        $subscription = null;
        $plan = null;

        if ($paymentId) {
            $payment = Payment::where('company_id', $companyId)
                ->where('payable_type', Subscription::class)
                ->find($paymentId);

            if ($payment) {
                $subscription = $payment->payable;
                $plan = $subscription?->plan;
            }
        } elseif ($subscriptionId) {
            $subscription = Subscription::where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->with('plan')
                ->find($subscriptionId);

            if ($subscription) {
                $plan = $subscription->plan;
                // Get the most recent successful payment
                $payment = Payment::where('company_id', $companyId)
                    ->where('payable_type', Subscription::class)
                    ->where('payable_id', $subscription->id)
                    ->where('status', PaymentConstants::PAYMENT_STATUS_SUCCESS)
                    ->latest()
                    ->first();
            }
        }

        // If no payment/subscription found, redirect to subscriptions index
        if (! $subscription) {
            return redirect()->route('user.subscriptions.index')
                ->with('info', 'Subscription not found.');
        }

        return view('user.subscriptions.success', [
            'payment' => $payment,
            'subscription' => $subscription,
            'plan' => $plan,
        ]);
    }
}
