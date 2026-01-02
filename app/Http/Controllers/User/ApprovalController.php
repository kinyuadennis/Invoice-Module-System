<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\ApprovalService;
use App\Models\ApprovalRequest;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Invoice;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApprovalController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService
    ) {}

    /**
     * Display pending approvals.
     */
    public function index(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();
        $type = $request->get('type'); // invoice, estimate, expense

        $pendingApprovals = $this->approvalService->getPendingApprovals($companyId, $type);

        return view('user.approvals.index', [
            'pendingApprovals' => $pendingApprovals,
            'type' => $type,
        ]);
    }

    /**
     * Request approval for an item.
     */
    public function request(Request $request, string $type, int $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $approvable = $this->getApprovable($type, $id, $companyId);

        try {
            $approvalRequest = $this->approvalService->requestApproval($approvable, $request);

            // Clear dashboard cache
            Cache::forget("dashboard_data_{$companyId}");

            return response()->json([
                'success' => true,
                'message' => 'Approval requested successfully.',
                'approval_request' => $approvalRequest,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to request approval: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve an approval request.
     */
    public function approve(Request $request, int $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $approvalRequest = ApprovalRequest::where('company_id', $companyId)
            ->with(['approvable'])
            ->findOrFail($id);

        // Check if user can approve
        if (! $this->approvalService->canApprove($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve items.',
            ], 403);
        }

        try {
            $approvalRequest = $this->approvalService->approve($approvalRequest, $request);

            // Clear dashboard cache
            Cache::forget("dashboard_data_{$companyId}");

            return response()->json([
                'success' => true,
                'message' => 'Approval request approved successfully.',
                'approval_request' => $approvalRequest,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject an approval request.
     */
    public function reject(Request $request, int $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $approvalRequest = ApprovalRequest::where('company_id', $companyId)
            ->with(['approvable'])
            ->findOrFail($id);

        // Check if user can approve
        if (! $this->approvalService->canApprove($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reject items.',
            ], 403);
        }

        try {
            $approvalRequest = $this->approvalService->reject($approvalRequest, $request);

            // Clear dashboard cache
            Cache::forget("dashboard_data_{$companyId}");

            return response()->json([
                'success' => true,
                'message' => 'Approval request rejected successfully.',
                'approval_request' => $approvalRequest,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an approval request.
     */
    public function cancel(Request $request, int $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $approvalRequest = ApprovalRequest::where('company_id', $companyId)
            ->findOrFail($id);

        try {
            $approvalRequest = $this->approvalService->cancel($approvalRequest, $request);

            // Clear dashboard cache
            Cache::forget("dashboard_data_{$companyId}");

            return response()->json([
                'success' => true,
                'message' => 'Approval request cancelled successfully.',
                'approval_request' => $approvalRequest,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approval history for an item.
     */
    public function history(Request $request, string $type, int $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $approvable = $this->getApprovable($type, $id, $companyId);

        $history = $this->approvalService->getApprovalHistory($approvable);

        return response()->json([
            'history' => $history,
        ]);
    }

    /**
     * Get approvable item by type and ID.
     */
    protected function getApprovable(string $type, int $id, int $companyId): Invoice|Estimate|Expense
    {
        return match ($type) {
            'invoice' => Invoice::where('company_id', $companyId)->findOrFail($id),
            'estimate' => Estimate::where('company_id', $companyId)->findOrFail($id),
            'expense' => Expense::where('company_id', $companyId)->findOrFail($id),
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }
}
