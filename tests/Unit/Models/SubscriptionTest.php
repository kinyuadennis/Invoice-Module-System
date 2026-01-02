<?php

namespace Tests\Unit\Models;

use App\Config\PaymentConstants;
use App\Config\SubscriptionConstants;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Subscription Model Tests
 *
 * Tests state machine transitions, invariants, and relationships.
 * Target: 100% coverage on state machines and invariant checks.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test valid state transitions.
     */

    /**
     * Test: Subscription can transition from PENDING to ACTIVE with successful payment.
     */
    public function test_subscription_can_transition_from_pending_to_active_with_payment(): void
    {
        $subscription = Subscription::factory()
            ->pending()
            ->create();

        // Create successful payment for subscription
        Payment::factory()
            ->success()
            ->forSubscription($subscription)
            ->create();

        // Transition to ACTIVE
        $subscription->transitionToActive();

        $this->assertTrue($subscription->isActive());
        $this->assertEquals(SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE, $subscription->status);
    }

    /**
     * Test: Subscription cannot transition to ACTIVE without successful payment.
     */
    public function test_subscription_cannot_transition_to_active_without_payment(): void
    {
        $subscription = Subscription::factory()
            ->pending()
            ->create();

        // No payment created

        // Attempt transition - should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot activate subscription without a successful payment');

        $subscription->transitionToActive();
    }

    /**
     * Test: Subscription can transition from ACTIVE to GRACE.
     */
    public function test_subscription_can_transition_from_active_to_grace(): void
    {
        $subscription = Subscription::factory()
            ->active()
            ->create();

        $subscription->transitionToGrace();

        $this->assertTrue($subscription->isInGrace());
        $this->assertEquals(SubscriptionConstants::SUBSCRIPTION_STATUS_GRACE, $subscription->status);
        $this->assertNotNull($subscription->ends_at);
    }

    /**
     * Test: Subscription can transition from GRACE to ACTIVE (renewal success).
     */
    public function test_subscription_can_transition_from_grace_to_active_with_payment(): void
    {
        $subscription = Subscription::factory()
            ->grace()
            ->create();

        // Create new successful payment for renewal
        Payment::factory()
            ->success()
            ->forSubscription($subscription)
            ->create();

        // Transition back to ACTIVE
        $subscription->transitionToActive();

        $this->assertTrue($subscription->isActive());
        $this->assertEquals(SubscriptionConstants::SUBSCRIPTION_STATUS_ACTIVE, $subscription->status);
    }

    /**
     * Test: Subscription can transition from GRACE to EXPIRED.
     */
    public function test_subscription_can_transition_from_grace_to_expired(): void
    {
        $subscription = Subscription::factory()
            ->grace()
            ->create();

        $subscription->transitionToExpired();

        $this->assertTrue($subscription->isExpired());
        $this->assertEquals(SubscriptionConstants::SUBSCRIPTION_STATUS_EXPIRED, $subscription->status);
    }

    /**
     * Test: Subscription can transition from ACTIVE to CANCELLED.
     */
    public function test_subscription_can_transition_from_active_to_cancelled(): void
    {
        $subscription = Subscription::factory()
            ->active()
            ->create();

        $subscription->transitionToCancelled();

        $this->assertTrue($subscription->isCancelled());
        $this->assertEquals(SubscriptionConstants::SUBSCRIPTION_STATUS_CANCELLED, $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);
    }

    /**
     * Test: Subscription cannot transition from EXPIRED to ACTIVE without new payment.
     */
    public function test_subscription_cannot_transition_from_expired_to_active_without_new_payment(): void
    {
        $subscription = Subscription::factory()
            ->expired()
            ->create();

        // Old payment exists but subscription is expired
        Payment::factory()
            ->success()
            ->forSubscription($subscription)
            ->create(['created_at' => now()->subMonths(2)]);

        // Attempt transition - should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot reactivate expired subscription without a new payment');

        $subscription->transitionToActive();
    }

    /**
     * Test: Subscription cannot transition from non-GRACE status to EXPIRED.
     */
    public function test_subscription_cannot_transition_to_expired_from_non_grace_status(): void
    {
        $subscription = Subscription::factory()
            ->active()
            ->create();

        // Attempt transition - should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can only expire subscription from GRACE status');

        $subscription->transitionToExpired();
    }

    /**
     * Test state check methods.
     */

    /**
     * Test: isPending() returns true for pending subscription.
     */
    public function test_is_pending_returns_true_for_pending_subscription(): void
    {
        $subscription = Subscription::factory()
            ->pending()
            ->create();

        $this->assertTrue($subscription->isPending());
    }

    /**
     * Test: isActive() returns true for active subscription.
     */
    public function test_is_active_returns_true_for_active_subscription(): void
    {
        $subscription = Subscription::factory()
            ->active()
            ->create();

        $this->assertTrue($subscription->isActive());
    }

    /**
     * Test: isInGrace() returns true for grace subscription.
     */
    public function test_is_in_grace_returns_true_for_grace_subscription(): void
    {
        $subscription = Subscription::factory()
            ->grace()
            ->create();

        $this->assertTrue($subscription->isInGrace());
    }

    /**
     * Test: isExpired() returns true for expired subscription.
     */
    public function test_is_expired_returns_true_for_expired_subscription(): void
    {
        $subscription = Subscription::factory()
            ->expired()
            ->create();

        $this->assertTrue($subscription->isExpired());
    }

    /**
     * Test: isCancelled() returns true for cancelled subscription.
     */
    public function test_is_cancelled_returns_true_for_cancelled_subscription(): void
    {
        $subscription = Subscription::factory()
            ->cancelled()
            ->create();

        $this->assertTrue($subscription->isCancelled());
    }

    /**
     * Test gateway immutability invariant.
     */

    /**
     * Test: Gateway field is immutable after subscription creation.
     */
    public function test_gateway_field_is_immutable_after_creation(): void
    {
        $subscription = Subscription::factory()
            ->mpesa()
            ->create();

        $originalGateway = $subscription->gateway;

        // Attempt to change gateway
        $subscription->gateway = PaymentConstants::GATEWAY_STRIPE;
        $subscription->save();

        // Gateway should remain unchanged
        $subscription->refresh();
        $this->assertEquals($originalGateway, $subscription->gateway);
        $this->assertEquals(PaymentConstants::GATEWAY_MPESA, $subscription->gateway);
    }

    /**
     * Test relationships.
     */

    /**
     * Test: Subscription belongs to user.
     */
    public function test_subscription_belongs_to_user(): void
    {
        $subscription = Subscription::factory()->create();

        $this->assertNotNull($subscription->user);
        $this->assertInstanceOf(\App\Models\User::class, $subscription->user);
    }

    /**
     * Test: Subscription belongs to company.
     */
    public function test_subscription_belongs_to_company(): void
    {
        $subscription = Subscription::factory()->create();

        $this->assertNotNull($subscription->company);
        $this->assertInstanceOf(\App\Models\Company::class, $subscription->company);
    }

    /**
     * Test: Subscription belongs to plan.
     */
    public function test_subscription_belongs_to_plan(): void
    {
        $subscription = Subscription::factory()->create();

        $this->assertNotNull($subscription->plan);
        $this->assertInstanceOf(\App\Models\SubscriptionPlan::class, $subscription->plan);
    }

    /**
     * Test: Subscription has many payments.
     */
    public function test_subscription_has_many_payments(): void
    {
        $subscription = Subscription::factory()->create();

        Payment::factory()
            ->count(3)
            ->forSubscription($subscription)
            ->create();

        $this->assertCount(3, $subscription->payments);
        $this->assertInstanceOf(Payment::class, $subscription->payments->first());
    }
}
