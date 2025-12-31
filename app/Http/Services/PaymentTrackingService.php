<?php

namespace App\Http\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;

class PaymentTrackingService
{
    /**
     * Track payment attempt.
     */
    public function trackAttempt(Payment $payment): Payment
    {
        $payment->increment('retry_count');
        $payment->update(['last_retry_at' => now()]);

        return $payment->fresh();
    }

    /**
     * Get payment analytics for a company.
     *
     * @return array<string, mixed>
     */
    public function getPaymentAnalytics(int $companyId, ?\Carbon\Carbon $startDate = null, ?\Carbon\Carbon $endDate = null): array
    {
        $cacheKey = "payment_analytics_{$companyId}_".($startDate?->format('Y-m-d') ?? 'all').'_'.($endDate?->format('Y-m-d') ?? 'all');

        return Cache::remember($cacheKey, 3600, function () use ($companyId, $startDate, $endDate) {
            $query = Payment::where('company_id', $companyId);

            if ($startDate) {
                $query->where('payment_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('payment_date', '<=', $endDate);
            }

            $payments = $query->get();

            $totalAmount = $payments->sum('amount');
            $totalCount = $payments->count();
            $avgAmount = $totalCount > 0 ? $totalAmount / $totalCount : 0;

            $byStatus = $payments->groupBy('status')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ];
            })->toArray();

            $byMethod = $payments->groupBy('payment_method')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ];
            })->toArray();

            $fraudStats = [
                'pending' => $payments->where('fraud_status', 'pending')->count(),
                'approved' => $payments->where('fraud_status', 'approved')->count(),
                'flagged' => $payments->where('fraud_status', 'flagged')->count(),
                'rejected' => $payments->where('fraud_status', 'rejected')->count(),
            ];

            return [
                'total_amount' => (float) $totalAmount,
                'total_count' => $totalCount,
                'average_amount' => (float) $avgAmount,
                'by_status' => $byStatus,
                'by_method' => $byMethod,
                'fraud_stats' => $fraudStats,
                'retry_count' => $payments->sum('retry_count'),
            ];
        });
    }

    /**
     * Get payment success rate.
     */
    public function getSuccessRate(int $companyId, ?\Carbon\Carbon $startDate = null, ?\Carbon\Carbon $endDate = null): float
    {
        $query = Payment::where('company_id', $companyId);

        if ($startDate) {
            $query->where('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('payment_date', '<=', $endDate);
        }

        $total = $query->count();
        $completed = $query->where('status', 'completed')->count();

        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    /**
     * Get flagged payments requiring review.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Payment>
     */
    public function getFlaggedPayments(int $companyId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return Payment::where('company_id', $companyId)
            ->whereIn('fraud_status', ['flagged', 'pending'])
            ->orderBy('fraud_score', 'desc')
            ->limit($limit)
            ->with(['invoice.client', 'invoice.company'])
            ->get();
    }
}
