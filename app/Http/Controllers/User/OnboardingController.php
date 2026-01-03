<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\InvoiceTemplate;
use App\Services\InvoicePrefixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding wizard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // If onboarding already completed, redirect to dashboard
        if ($user->onboarding_completed) {
            return redirect()->route('user.dashboard');
        }

        // Get current step from session or default to 1
        $step = (int) $request->session()->get('onboarding_step', 1);

        // If user has companies, skip to step 5 (preferences) or 6 (complete)
        if ($user->ownedCompanies()->count() > 0 && $step < 5) {
            $step = 5;
            $request->session()->put('onboarding_step', $step);
        }

        return $this->showStep($request, $step);
    }

    /**
     * Show a specific onboarding step.
     */
    public function showStep(Request $request, int $step): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        return $this->renderStep($request, $step);
    }

    /**
     * Render a specific onboarding step view.
     */
    private function renderStep(Request $request, int $step): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $totalSteps = 6;

        // Validate step number
        if ($step < 1 || $step > $totalSteps) {
            return redirect()->route('user.onboarding.index');
        }

        // Update session with current step
        $request->session()->put('onboarding_step', $step);

        $data = [
            'step' => $step,
            'totalSteps' => $totalSteps,
            'progress' => round(($step / $totalSteps) * 100),
        ];

        // Step-specific data
        switch ($step) {
            case 1:
                // Welcome step - no additional data needed
                break;

            case 2:
                // Company basics
                $data['company'] = $user->ownedCompanies()->first();
                break;

            case 3:
                // Business details
                $data['company'] = $user->ownedCompanies()->first();
                break;

            case 4:
                // Logo & branding
                $data['company'] = $user->ownedCompanies()->first();
                break;

            case 5:
                // Invoice preferences
                $data['company'] = $user->ownedCompanies()->first();
                $data['templates'] = InvoiceTemplate::active()->ordered()->get();
                break;

            case 6:
                // ETIMS check & complete
                $data['company'] = $user->ownedCompanies()->first();
                break;
        }

        return view("user.onboarding.step-{$step}", $data);
    }

    /**
     * Handle step submission and move to next step.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $step = (int) $request->input('step', 1);
        $action = $request->input('action', 'next'); // 'next', 'skip', 'back'

        // Handle back action
        if ($action === 'back') {
            $newStep = max(1, $step - 1);
            $request->session()->put('onboarding_step', $newStep);

            return redirect()->route('user.onboarding.step', ['step' => $newStep]);
        }

        // Handle skip action
        if ($action === 'skip') {
            $newStep = min(6, $step + 1);
            $request->session()->put('onboarding_step', $newStep);

            return redirect()->route('user.onboarding.step', ['step' => $newStep]);
        }

        // Handle step-specific validation and processing
        switch ($step) {
            case 2:
                // Company basics
                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'phone' => 'nullable|string|max:20',
                ]);

                $company = $this->createOrUpdateCompany($user, $validated, $step);
                break;

            case 3:
                // Business details
                $validated = $request->validate([
                    'address' => 'nullable|string|max:500',
                    'kra_pin' => 'nullable|string|max:11',
                    'currency' => 'nullable|string|size:3',
                    'timezone' => 'nullable|string|max:50',
                ]);

                if (isset($validated['kra_pin'])) {
                    $validated['kra_pin'] = strtoupper($validated['kra_pin']);
                }

                $company = $this->createOrUpdateCompany($user, $validated, $step);
                break;

            case 4:
                // Logo & branding
                $validated = $request->validate([
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]);

                $company = $user->ownedCompanies()->first();
                if ($company && $request->hasFile('logo')) {
                    // Delete old logo if exists
                    if ($company->logo) {
                        Storage::disk('public')->delete($company->logo);
                    }

                    $logo = $request->file('logo');
                    $path = $logo->store('companies/logos', 'public');
                    $company->update(['logo' => $path]);
                }
                break;

            case 5:
                // Invoice preferences
                $validated = $request->validate([
                    'invoice_prefix' => 'nullable|string|max:10|alpha',
                    'default_invoice_template_id' => 'nullable|exists:invoice_templates,id',
                ]);

                $company = $user->ownedCompanies()->first();
                if ($company) {
                    $company->update($validated);

                    // Create invoice prefix if doesn't exist
                    $prefixService = app(InvoicePrefixService::class);
                    if (! $company->invoicePrefixes()->exists()) {
                        $prefixService->createDefaultPrefix($company, $user->id);
                    }
                }
                break;

            case 6:
                // Complete onboarding
                $user->update(['onboarding_completed' => true]);
                $request->session()->forget('onboarding_step');

                // Check if user registered with a plan selected (redirect to checkout)
                $pendingPlanId = $request->session()->get('pending_subscription_plan');
                if ($pendingPlanId) {
                    $request->session()->forget('pending_subscription_plan');

                    return redirect()->route('user.subscriptions.checkout', ['plan' => $pendingPlanId])
                        ->with('success', 'Welcome! Complete your subscription setup.');
                }

                return redirect()->route('user.dashboard')
                    ->with('success', 'Welcome! Your account is set up and ready to use.');
        }

        // Move to next step
        $newStep = min(6, $step + 1);
        $request->session()->put('onboarding_step', $newStep);

        return redirect()->route('user.onboarding.step', ['step' => $newStep])
            ->with('success', 'Step completed successfully!');
    }

    /**
     * Create or update company during onboarding.
     */
    private function createOrUpdateCompany($user, array $data, int $step): ?Company
    {
        $company = $user->ownedCompanies()->first();

        if (! $company) {
            // Create new company
            $data['owner_user_id'] = $user->id;
            $data['currency'] = $data['currency'] ?? 'KES';
            $data['timezone'] = $data['timezone'] ?? 'Africa/Nairobi';
            $data['next_invoice_sequence'] = 1;
            $data['invoice_prefix'] = $data['invoice_prefix'] ?? 'INV';

            $company = Company::create($data);

            // Set as active company
            Session::put('active_company_id', $company->id);
            if (! $user->active_company_id) {
                $user->update(['active_company_id' => $company->id]);
            }
            if (! $user->company_id) {
                $user->update(['company_id' => $company->id]);
            }
        } else {
            // Update existing company
            $company->update($data);
        }

        return $company;
    }

    /**
     * Complete onboarding early (skip remaining steps).
     */
    public function complete(Request $request)
    {
        $user = Auth::user();

        // Ensure user has at least one company
        if ($user->ownedCompanies()->count() === 0) {
            return redirect()->route('user.onboarding.step', ['step' => 2])
                ->with('error', 'Please create a company first.');
        }

        $user->update(['onboarding_completed' => true]);
        $request->session()->forget('onboarding_step');

        // Check if user registered with a plan selected (redirect to checkout)
        $pendingPlanId = $request->session()->get('pending_subscription_plan');
        if ($pendingPlanId) {
            $request->session()->forget('pending_subscription_plan');

            return redirect()->route('user.subscriptions.checkout', ['plan' => $pendingPlanId])
                ->with('success', 'Welcome! Complete your subscription setup.');
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Welcome! Your account is set up and ready to use.');
    }
}
