<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Traits\FormatsInvoiceNumber;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    use FormatsInvoiceNumber;

    public function index()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('user.dashboard');
        }

        // Load 6 recent invoices with relationships
        $controller = $this;
        $recentInvoices = Invoice::with(['client', 'invoiceItems', 'platformFees'])
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

        // Load 4 demo clients
        $demoClients = Client::take(4)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'initials' => self::getInitials($client->name),
                ];
            });

        return view('public.home', [
            'recentInvoices' => $recentInvoices,
            'demoClients' => $demoClients,
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
}
