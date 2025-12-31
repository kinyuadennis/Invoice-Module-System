<?php

namespace App\Http\Services;

use App\Models\Payment;
use Illuminate\Http\Request;

class FraudDetectionService
{
    /**
     * Analyze a payment for fraud indicators.
     *
     * @return array<string, mixed>
     */
    public function analyzePayment(Payment $payment, ?Request $request = null): array
    {
        $checks = [];
        $score = 0;
        $maxScore = 100;
        $reasons = [];

        // Check 1: Unusual amount (check against average invoice amount)
        $invoice = $payment->invoice;
        if ($invoice && $invoice->company) {
            $avgAmount = $invoice->company->invoices()
                ->where('status', '!=', 'draft')
                ->avg('grand_total');

            if ($avgAmount && $payment->amount > ($avgAmount * 3)) {
                $checks['unusual_amount'] = true;
                $score += 20;
                $reasons[] = 'Payment amount significantly higher than average';
            } else {
                $checks['unusual_amount'] = false;
            }
        }

        // Check 2: Rapid successive payments from same IP
        if ($payment->ip_address) {
            $recentPayments = Payment::where('ip_address', $payment->ip_address)
                ->where('created_at', '>=', now()->subHours(1))
                ->where('id', '!=', $payment->id)
                ->count();

            if ($recentPayments >= 5) {
                $checks['rapid_payments'] = true;
                $score += 25;
                $reasons[] = 'Multiple payments from same IP in short time';
            } else {
                $checks['rapid_payments'] = false;
            }
        }

        // Check 3: Payment from blacklisted IP (if you have IP blacklist)
        if ($payment->ip_address && $this->isBlacklistedIp($payment->ip_address)) {
            $checks['blacklisted_ip'] = true;
            $score += 50;
            $reasons[] = 'Payment from blacklisted IP address';
        } else {
            $checks['blacklisted_ip'] = false;
        }

        // Check 4: User agent mismatch or suspicious
        if ($payment->user_agent) {
            $isSuspicious = $this->isSuspiciousUserAgent($payment->user_agent);
            if ($isSuspicious) {
                $checks['suspicious_user_agent'] = true;
                $score += 15;
                $reasons[] = 'Suspicious user agent detected';
            } else {
                $checks['suspicious_user_agent'] = false;
            }
        }

        // Check 5: Payment retry count
        if ($payment->retry_count > 3) {
            $checks['high_retry_count'] = true;
            $score += 10;
            $reasons[] = 'Multiple payment retries detected';
        } else {
            $checks['high_retry_count'] = false;
        }

        // Check 6: Payment outside business hours (optional - depends on timezone)
        $paymentHour = $payment->paid_at ? $payment->paid_at->hour : now()->hour;
        if ($paymentHour < 6 || $paymentHour > 23) {
            $checks['off_hours'] = true;
            $score += 5;
            $reasons[] = 'Payment made outside normal business hours';
        } else {
            $checks['off_hours'] = false;
        }

        // Determine status based on score
        $status = 'approved';
        if ($score >= 50) {
            $status = 'rejected';
        } elseif ($score >= 30) {
            $status = 'flagged';
        } elseif ($score > 0) {
            $status = 'pending';
        }

        return [
            'fraud_score' => min($score, $maxScore),
            'fraud_status' => $status,
            'fraud_checks' => $checks,
            'fraud_reason' => ! empty($reasons) ? implode('; ', $reasons) : null,
        ];
    }

    /**
     * Check if IP is blacklisted.
     */
    protected function isBlacklistedIp(string $ip): bool
    {
        // Implement your IP blacklist logic here
        // For now, return false (no blacklist)
        return false;
    }

    /**
     * Check if user agent is suspicious.
     */
    protected function isSuspiciousUserAgent(string $userAgent): bool
    {
        // Check for common bot/suspicious user agents
        $suspiciousPatterns = [
            'bot',
            'crawler',
            'spider',
            'scraper',
            'curl',
            'wget',
        ];

        $userAgentLower = strtolower($userAgent);

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($userAgentLower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply fraud detection to a payment.
     */
    public function checkPayment(Payment $payment, ?Request $request = null): Payment
    {
        // Capture request data if available
        if ($request) {
            $payment->ip_address = $request->ip();
            $payment->user_agent = $request->userAgent();
            $payment->save();
        }

        $analysis = $this->analyzePayment($payment, $request);

        $payment->update([
            'fraud_status' => $analysis['fraud_status'],
            'fraud_score' => $analysis['fraud_score'],
            'fraud_checks' => $analysis['fraud_checks'],
            'fraud_reason' => $analysis['fraud_reason'],
        ]);

        return $payment->fresh();
    }

    /**
     * Manually review and approve/reject a flagged payment.
     */
    public function reviewPayment(Payment $payment, string $status, ?string $reason = null): Payment
    {
        if (! in_array($status, ['approved', 'rejected'])) {
            throw new \InvalidArgumentException('Invalid review status. Must be approved or rejected.');
        }

        $payment->update([
            'fraud_status' => $status,
            'fraud_reason' => $reason ?? $payment->fraud_reason,
            'fraud_reviewed_at' => now(),
            'fraud_reviewed_by' => auth()->id(),
        ]);

        return $payment->fresh();
    }
}
