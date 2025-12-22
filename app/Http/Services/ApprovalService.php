<?php

namespace App\Http\Services;

use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Invoice;
use App\Services\CurrentCompanyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    /**
     * Request approval for an invoice, estimate, or expense.
     */
    public function requestApproval(Model $approvable, Request $request): ApprovalRequest
    {
        $companyId = CurrentCompanyService::requireId();
        $user = $request->user();

        // Validate approvable type
        if (! $this->isApprovable($approvable)) {
            throw ValidationException::withMessages([
                'approvable' => 'This item cannot be submitted for approval.',
            ]);
        }

        // Check if already has pending approval
        $existingRequest = ApprovalRequest::where('company_id', $companyId)
            ->where('approvable_type', get_class($approvable))
            ->where('approvable_id', $approvable->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            throw ValidationException::withMessages([
                'approval' => 'This item already has a pending approval request.',
            ]);
        }

        return DB::transaction(function () use ($companyId, $approvable, $user, $request) {
            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'company_id' => $companyId,
                'approvable_type' => get_class($approvable),
                'approvable_id' => $approvable->id,
                'requested_by_user_id' => $user->id,
                'status' => 'pending',
                'notes' => $request->input('notes'),
            ]);

            // Update approvable item
            $approvable->requires_approval = true;
            $approvable->approval_status = 'pending';
            $approvable->save();

            // Create history record
            $this->createHistory($approvalRequest, $user, 'requested', $request->input('notes'));

            return $approvalRequest;
        });
    }

    /**
     * Approve an approval request.
     */
    public function approve(ApprovalRequest $approvalRequest, Request $request): ApprovalRequest
    {
        $user = $request->user();

        if (! $approvalRequest->isPending()) {
            throw ValidationException::withMessages([
                'approval' => 'This approval request is not pending.',
            ]);
        }

        return DB::transaction(function () use ($approvalRequest, $user, $request) {
            // Update approval request
            $approvalRequest->status = 'approved';
            $approvalRequest->approved_by_user_id = $user->id;
            $approvalRequest->approved_at = now();
            $approvalRequest->notes = $request->input('notes', $approvalRequest->notes);
            $approvalRequest->save();

            // Update approvable item
            $approvable = $approvalRequest->approvable;
            $approvable->approval_status = 'approved';
            $approvable->save();

            // Create history record
            $this->createHistory($approvalRequest, $user, 'approved', $request->input('notes'));

            return $approvalRequest;
        });
    }

    /**
     * Reject an approval request.
     */
    public function reject(ApprovalRequest $approvalRequest, Request $request): ApprovalRequest
    {
        $user = $request->user();

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if (! $approvalRequest->isPending()) {
            throw ValidationException::withMessages([
                'approval' => 'This approval request is not pending.',
            ]);
        }

        return DB::transaction(function () use ($approvalRequest, $user, $request) {
            // Update approval request
            $approvalRequest->status = 'rejected';
            $approvalRequest->rejected_by_user_id = $user->id;
            $approvalRequest->rejected_at = now();
            $approvalRequest->rejection_reason = $request->input('rejection_reason');
            $approvalRequest->notes = $request->input('notes', $approvalRequest->notes);
            $approvalRequest->save();

            // Update approvable item
            $approvable = $approvalRequest->approvable;
            $approvable->approval_status = 'rejected';
            $approvable->save();

            // Create history record
            $this->createHistory($approvalRequest, $user, 'rejected', $request->input('rejection_reason'));

            return $approvalRequest;
        });
    }

    /**
     * Cancel an approval request.
     */
    public function cancel(ApprovalRequest $approvalRequest, Request $request): ApprovalRequest
    {
        $user = $request->user();

        // Only requester or admin can cancel
        if ($approvalRequest->requested_by_user_id !== $user->id && ! $this->canApprove($user)) {
            throw ValidationException::withMessages([
                'approval' => 'You do not have permission to cancel this approval request.',
            ]);
        }

        if (! $approvalRequest->isPending()) {
            throw ValidationException::withMessages([
                'approval' => 'Only pending approval requests can be cancelled.',
            ]);
        }

        return DB::transaction(function () use ($approvalRequest, $user, $request) {
            // Update approval request
            $approvalRequest->status = 'cancelled';
            $approvalRequest->save();

            // Update approvable item
            $approvable = $approvalRequest->approvable;
            $approvable->approval_status = null;
            $approvable->requires_approval = false;
            $approvable->save();

            // Create history record
            $this->createHistory($approvalRequest, $user, 'cancelled', $request->input('notes'));

            return $approvalRequest;
        });
    }

    /**
     * Get pending approvals for a company.
     */
    public function getPendingApprovals(int $companyId, ?string $type = null): array
    {
        $query = ApprovalRequest::where('company_id', $companyId)
            ->where('status', 'pending')
            ->with(['approvable', 'requestedBy']);

        if ($type) {
            $modelClass = match ($type) {
                'invoice' => Invoice::class,
                'estimate' => Estimate::class,
                'expense' => Expense::class,
                default => null,
            };

            if ($modelClass) {
                $query->where('approvable_type', $modelClass);
            }
        }

        return $query->latest()
            ->get()
            ->map(function ($request) {
                return $this->formatApprovalRequest($request);
            })
            ->toArray();
    }

    /**
     * Get approval history for an item.
     */
    public function getApprovalHistory(Model $approvable): array
    {
        $requests = ApprovalRequest::where('approvable_type', get_class($approvable))
            ->where('approvable_id', $approvable->id)
            ->with(['requestedBy', 'approvedBy', 'rejectedBy', 'history.user'])
            ->latest()
            ->get();

        return $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'status' => $request->status,
                'requested_by' => $request->requestedBy?->name,
                'requested_at' => $request->created_at->format('Y-m-d H:i:s'),
                'approved_by' => $request->approvedBy?->name,
                'approved_at' => $request->approved_at?->format('Y-m-d H:i:s'),
                'rejected_by' => $request->rejectedBy?->name,
                'rejected_at' => $request->rejected_at?->format('Y-m-d H:i:s'),
                'rejection_reason' => $request->rejection_reason,
                'notes' => $request->notes,
                'history' => $request->history->map(function ($history) {
                    return [
                        'action' => $history->action,
                        'user' => $history->user->name,
                        'comment' => $history->comment,
                        'created_at' => $history->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ];
        })->toArray();
    }

    /**
     * Check if user can approve items.
     */
    public function canApprove($user): bool
    {
        // Check if user has approval permission or is company owner
        $companyId = CurrentCompanyService::getId();
        if (! $companyId) {
            return false;
        }

        // Company owner can always approve
        $company = \App\Models\Company::find($companyId);
        if ($company && $company->owner_user_id === $user->id) {
            return true;
        }

        // Check if user has approval permission through roles
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission('approve-invoices', $companyId) ||
                   $user->hasPermission('approve-estimates', $companyId) ||
                   $user->hasPermission('approve-expenses', $companyId);
        }

        return false;
    }

    /**
     * Check if item is approvable.
     */
    protected function isApprovable(Model $approvable): bool
    {
        return $approvable instanceof Invoice ||
               $approvable instanceof Estimate ||
               $approvable instanceof Expense;
    }

    /**
     * Create approval history record.
     */
    protected function createHistory(ApprovalRequest $approvalRequest, $user, string $action, ?string $comment = null): ApprovalHistory
    {
        return ApprovalHistory::create([
            'approval_request_id' => $approvalRequest->id,
            'user_id' => $user->id,
            'action' => $action,
            'comment' => $comment,
        ]);
    }

    /**
     * Format approval request for display.
     */
    protected function formatApprovalRequest(ApprovalRequest $request): array
    {
        $approvable = $request->approvable;

        return [
            'id' => $request->id,
            'approvable_type' => class_basename($request->approvable_type),
            'approvable_id' => $request->approvable_id,
            'approvable' => $this->formatApprovable($approvable),
            'requested_by' => $request->requestedBy?->name,
            'requested_at' => $request->created_at->format('Y-m-d H:i:s'),
            'status' => $request->status,
            'notes' => $request->notes,
        ];
    }

    /**
     * Format approvable item for display.
     */
    protected function formatApprovable(?Model $approvable): ?array
    {
        if (! $approvable) {
            return null;
        }

        if ($approvable instanceof Invoice) {
            return [
                'id' => $approvable->id,
                'number' => $approvable->invoice_number ?? 'INV-'.str_pad($approvable->id, 3, '0', STR_PAD_LEFT),
                'client' => $approvable->client?->name,
                'amount' => (float) $approvable->grand_total,
                'status' => $approvable->status,
            ];
        }

        if ($approvable instanceof Estimate) {
            return [
                'id' => $approvable->id,
                'number' => $approvable->estimate_number ?? 'EST-'.str_pad($approvable->id, 3, '0', STR_PAD_LEFT),
                'client' => $approvable->client?->name,
                'amount' => (float) $approvable->grand_total,
                'status' => $approvable->status,
            ];
        }

        if ($approvable instanceof Expense) {
            return [
                'id' => $approvable->id,
                'number' => $approvable->expense_number,
                'description' => $approvable->description,
                'amount' => (float) $approvable->amount,
                'status' => $approvable->status,
            ];
        }

        return null;
    }
}
