<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    /**
     * Store feedback submission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:general,bug,feature,other',
            'message' => 'required|string|min:10|max:2000',
            'anonymous' => 'nullable|boolean',
        ]);

        $companyId = CurrentCompanyService::requireId();

        $feedback = Feedback::create([
            'user_id' => $validated['anonymous'] ? null : Auth::id(),
            'company_id' => $companyId,
            'type' => $validated['type'],
            'message' => $validated['message'],
            'anonymous' => $validated['anonymous'] ?? false,
            'status' => 'new',
        ]);

        // Send email notification to admins (optional - implement if needed)
        // Mail::to(config('mail.admin_email'))->send(new FeedbackNotification($feedback));

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback! We appreciate your input.',
        ]);
    }
}
