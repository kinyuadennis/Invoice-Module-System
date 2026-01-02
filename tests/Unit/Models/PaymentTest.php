<?php

namespace Tests\Unit\Models;

use App\Config\PaymentConstants;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Payment Model Tests
 *
 * Tests state machine transitions, invariants, and relationships.
 * Target: 100% coverage on state machines and invariant checks.
 *
 * Reference: InvoiceHub Payment & Subscription Module Blueprint v1
 */
class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test valid state transitions.
     */

    /**
     * Test: Payment can transition from INITIATED to SUCCESS.
     */
    public function test_payment_can_transition_from_initiated_to_success(): void
    {
        $payment = Payment::factory()
            ->initiated()
            ->create();

        $payment->markAsSuccess();

        $this->assertEquals(PaymentConstants::PAYMENT_STATUS_SUCCESS, $payment->status);
        $this->assertTrue($payment->isTerminal());
        $this->assertNotNull($payment->paid_at);
    }

    /**
     * Test: Payment can transition from INITIATED to FAILED.
     */
    public function test_payment_can_transition_from_initiated_to_failed(): void
    {
        $payment = Payment::factory()
            ->initiated()
            ->create();

        $payment->markAsFailed();

        $this->assertEquals(PaymentConstants::PAYMENT_STATUS_FAILED, $payment->status);
        $this->assertTrue($payment->isTerminal());
    }

    /**
     * Test: Payment can transition from INITIATED to TIMEOUT.
     */
    public function test_payment_can_transition_from_initiated_to_timeout(): void
    {
        $payment = Payment::factory()
            ->initiated()
            ->create();

        $payment->markAsTimeout();

        $this->assertEquals(PaymentConstants::PAYMENT_STATUS_TIMEOUT, $payment->status);
        $this->assertTrue($payment->isTerminal());
    }

    /**
     * Test: Payment cannot transition twice to terminal state (SUCCESS).
     */
    public function test_payment_cannot_transition_twice_to_success(): void
    {
        $payment = Payment::factory()
            ->success()
            ->create();

        // Attempt to transition again - should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment is already in terminal state');

        $payment->markAsSuccess();
    }

    /**
     * Test: Payment cannot transition twice to terminal state (FAILED).
     */
    public function test_payment_cannot_transition_twice_to_failed(): void
    {
        $payment = Payment::factory()
            ->failed()
            ->create();

        // Attempt to transition again - should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment is already in a terminal state');

        $payment->markAsFailed();
    }

    /**
     * Test: Payment cannot transition from terminal state to another terminal state.
     */
    public function test_payment_cannot_transition_from_success_to_failed(): void
    {
        $payment = Payment::factory()
            ->success()
            ->create();

        // Attempt to transition to failed - should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment is already in a terminal state');

        $payment->markAsFailed();
    }

    /**
     * Test: Payment cannot transition from FAILED to SUCCESS.
     */
    public function test_payment_cannot_transition_from_failed_to_success(): void
    {
        $payment = Payment::factory()
            ->failed()
            ->create();

        // Attempt to transition to success - should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment is already in a terminal state');

        $payment->markAsSuccess();
    }

    /**
     * Test: Payment cannot transition from TIMEOUT to SUCCESS.
     */
    public function test_payment_cannot_transition_from_timeout_to_success(): void
    {
        $payment = Payment::factory()
            ->timeout()
            ->create();

        // Attempt to transition to success - should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment is already in a terminal state');

        $payment->markAsSuccess();
    }

    /**
     * Test: isTerminal() returns true for terminal states.
     */
    public function test_is_terminal_returns_true_for_terminal_states(): void
    {
        $successPayment = Payment::factory()->success()->create();
        $failedPayment = Payment::factory()->failed()->create();
        $timeoutPayment = Payment::factory()->timeout()->create();

        $this->assertTrue($successPayment->isTerminal());
        $this->assertTrue($failedPayment->isTerminal());
        $this->assertTrue($timeoutPayment->isTerminal());
    }

    /**
     * Test: isTerminal() returns false for INITIATED state.
     */
    public function test_is_terminal_returns_false_for_initiated_state(): void
    {
        $payment = Payment::factory()->initiated()->create();

        $this->assertFalse($payment->isTerminal());
    }

    /**
     * Test polymorphic relationships.
     */

    /**
     * Test: Payment can belong to subscription (polymorphic).
     */
    public function test_payment_can_belong_to_subscription(): void
    {
        $subscription = Subscription::factory()->create();
        $payment = Payment::factory()
            ->forSubscription($subscription)
            ->create();

        $this->assertEquals(Subscription::class, $payment->payable_type);
        $this->assertEquals($subscription->id, $payment->payable_id);
        $this->assertInstanceOf(Subscription::class, $payment->payable);
        $this->assertEquals($subscription->id, $payment->payable->id);
    }

    /**
     * Test: Payment subscription relationship works.
     */
    public function test_payment_subscription_relationship_works(): void
    {
        $subscription = Subscription::factory()->create();
        $payment = Payment::factory()
            ->forSubscription($subscription)
            ->create();

        $this->assertInstanceOf(Subscription::class, $payment->subscription);
        $this->assertEquals($subscription->id, $payment->subscription->id);
    }
}
