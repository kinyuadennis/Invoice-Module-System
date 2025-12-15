<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Services\PlatformFeeService;
use App\Models\Client;
use App\Models\Invoice;
use App\Traits\FormatsInvoiceNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use FormatsInvoiceNumber;

    public function index()
    {
        // Always show landing page when Home link is clicked (has view=landing parameter)
        // This takes priority over any auth redirects
        if (request()->has('view') && request()->get('view') === 'landing') {
            // Always show landing page when explicitly requested via Home link
            // Continue to load landing page data below
        } elseif (Auth::check()) {
            // Check if coming from internal navigation (referer from same domain)
            $referer = request()->header('Referer');
            $refererHost = $referer ? parse_url($referer, PHP_URL_HOST) : null;
            $currentHost = request()->getHost();
            $isInternalNavigation = $refererHost && $refererHost === $currentHost;

            // Only auto-redirect on direct access (typing URL directly, no referer, no view param)
            if (! $isInternalNavigation) {
                if (Auth::user()->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->route('user.dashboard');
            }
            // If internal navigation, continue to show landing page
        }

        // Load 6 recent invoices for social proof (mix of paid, sent, overdue)
        $controller = $this;
        $recentInvoices = Invoice::with(['client', 'invoiceItems', 'platformFees'])
            ->whereIn('status', ['paid', 'sent', 'overdue'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($invoice) use ($controller) {
                $platformFee = $invoice->platformFees->first();
                $invoiceNumber = $controller->formatInvoiceNumber($invoice->id);

                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoiceNumber,
                    'status' => $invoice->status,
                    'client_name' => $invoice->client->name ?? 'Unknown',
                    'total' => (float) $invoice->total,
                    'due_date' => $invoice->due_date,
                    'platform_fee' => $platformFee ? (float) $platformFee->fee_amount : 0,
                    'created_at' => $invoice->created_at,
                ];
            });

        // Load all demo clients for hero selector
        $allClients = Client::select('id', 'name', 'email')
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'initials' => self::getInitials($client->name),
                ];
            });

        // Stats for hero section
        $stats = [
            'businesses' => 500,
            'invoicesToday' => Invoice::whereDate('created_at', today())->count() ?: 12,
            'avgPaymentDays' => 7,
        ];

        // How It Works steps
        $steps = [
            [
                'number' => 1,
                'title' => 'Create Professional Invoice',
                'time' => '60 seconds',
                'description' => 'Add your client, select services, and our system auto-calculates totals including VAT and platform fee.',
                'icon' => '<svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                'outcome' => 'Professional invoice ready',
            ],
            [
                'number' => 2,
                'title' => 'Send via M-Pesa, Email, or WhatsApp',
                'time' => 'Instant',
                'description' => 'One click sends the invoice. Your client receives it immediately with M-Pesa payment link.',
                'icon' => '<div class="flex gap-2"><div class="w-8 h-8 bg-green-500 rounded"></div><div class="w-8 h-8 bg-blue-500 rounded"></div><div class="w-8 h-8 bg-purple-500 rounded"></div></div>',
                'outcome' => 'Invoice delivered instantly',
            ],
            [
                'number' => 3,
                'title' => 'Get Paid Faster',
                'time' => '7 days average',
                'description' => 'Automated reminders ensure you get paid. Track everything in one dashboard.',
                'icon' => '<svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
                'outcome' => 'Payment received in 7 days',
            ],
        ];

        // Features data
        $features = [
            [
                'name' => 'M-Pesa Auto-Reconciliation',
                'category' => 'payment',
                'description' => 'Automatically match M-Pesa payments to invoices. No manual entry needed.',
                'benefit' => 'Save 2 hours/week on manual reconciliation',
                'badge' => 'M-Pesa Verified',
                'icon' => '<svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
                'metric' => 'Auto-match 95% of payments',
            ],
            [
                'name' => 'KRA eTIMS Compliance',
                'category' => 'compliance',
                'description' => 'Generate KRA-compliant invoices automatically. Export for eTIMS submission in one click.',
                'benefit' => '100% KRA compliant invoices',
                'badge' => 'KRA Ready',
                'icon' => '<svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
            ],
            [
                'name' => 'Payment Behavior Analytics',
                'category' => 'analytics',
                'description' => 'See which clients pay fastest. Identify payment patterns and optimize your cash flow.',
                'benefit' => 'Average payment time: 7 days',
                'icon' => '<svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
            ],
            [
                'name' => 'Cash Flow Insights',
                'category' => 'analytics',
                'description' => 'Forecast cash flow based on pending invoices. Know exactly when money is coming in.',
                'benefit' => 'KES 2.5M expected this month',
                'icon' => '<svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
            ],
            [
                'name' => 'Multi-Currency Support',
                'category' => 'payment',
                'description' => 'Invoice in KES, USD, EUR, GBP. Auto-convert for international clients.',
                'badge' => 'Live Exchange Rates',
                'icon' => '<svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            ],
            [
                'name' => 'Automated Reminders',
                'category' => 'automation',
                'description' => 'Send payment reminders automatically. Reduce overdue invoices by 60%.',
                'benefit' => '3x faster payments',
                'icon' => '<svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
            ],
        ];

        // Pricing plans
        $plans = [
            [
                'name' => 'Free',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'popular' => false,
                'features' => [
                    '3 invoices/month',
                    'Basic templates',
                    'Email support',
                    'PDF export',
                ],
                'cta' => 'Start Free',
                'social_proof' => null,
            ],
            [
                'name' => 'Starter',
                'price_monthly' => 999,
                'price_yearly' => 9592, // 999 * 12 * 0.8 (20% discount)
                'popular' => true,
                'features' => [
                    'Unlimited invoices',
                    'M-Pesa integration',
                    'Auto reminders',
                    'Client portal',
                    'Priority support',
                ],
                'cta' => 'Start 14-Day Trial',
                'social_proof' => '47 businesses',
            ],
            [
                'name' => 'Pro',
                'price_monthly' => 2999,
                'price_yearly' => 28790, // 2999 * 12 * 0.8
                'popular' => false,
                'features' => [
                    'Everything in Starter',
                    'KRA eTIMS export (ready)',
                    'Recurring billing',
                    'Advanced analytics',
                    'API access',
                    'Dedicated support',
                ],
                'cta' => 'Start 14-Day Trial',
                'social_proof' => null,
            ],
        ];

        // Reviews are now loaded dynamically via API endpoint /api/reviews
        // No need to pass testimonials to the view

        // Hero heading variants for A/B testing
        $heroHeadingVariants = [
            'variant1' => 'Professional invoicing for Kenyan businesses — compliant, simple, reliable.',
            'variant2' => 'Create invoices, collect payments, stay KRA-compliant — in minutes.',
            'variant3' => 'Invoices that get paid faster — simple setup, custom templates, MPesa-ready.',
        ];

        // Get variant from query parameter or default to variant1
        $heroVariant = request()->get('hero', 'variant1');
        $heroHeading = $heroHeadingVariants[$heroVariant] ?? $heroHeadingVariants['variant1'];

        return view('public.home', [
            'recentInvoices' => $recentInvoices,
            'allClients' => $allClients,
            'stats' => $stats,
            'steps' => $steps,
            'features' => $features,
            'plans' => $plans,
            'heroHeading' => $heroHeading,
        ]);
    }

    /**
     * Get initials from client name
     */
    private static function getInitials(string $name): string
    {
        $words = explode(' ', $name);
        $initials = '';

        foreach ($words as $word) {
            if (! empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }

        return substr($initials, 0, 2);
    }

    public function about()
    {
        return view('public.about');
    }

    public function pricing()
    {
        return view('public.pricing');
    }

    /**
     * Calculate invoice preview totals (AJAX endpoint)
     */
    public function calculatePreview(Request $request, PlatformFeeService $platformFeeService)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        $items = $request->input('items');
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += (float) $item['amount'];
        }

        $tax = $subtotal * 0.16; // 16% VAT
        $totalBeforeFee = $subtotal + $tax;
        $platformFee = $platformFeeService->calculateFee($totalBeforeFee);
        $total = $totalBeforeFee + $platformFee;

        return response()->json([
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'tax_rate' => 16,
            'platform_fee' => round($platformFee, 2),
            'platform_fee_rate' => 0.8,
            'total' => round($total, 2),
        ]);
    }
}
