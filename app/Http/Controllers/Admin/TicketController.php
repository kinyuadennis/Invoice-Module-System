<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\TicketService;
use App\Models\Ticket;
use App\TicketStatus;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $ticketService
    ) {}

    /**
     * Display a listing of tickets.
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'assignedUser', 'company']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by assigned user
        if ($request->has('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->latest()->paginate(20);

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'priority', 'category', 'assigned_to', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        return view('admin.tickets.create');
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|in:technical,billing,feature_request,bug,general',
            'company_id' => 'nullable|exists:companies,id',
            'user_id' => 'nullable|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket = $this->ticketService->createTicket($validated);

        return redirect()->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Ticket created successfully.');
    }

    /**
     * Display the specified ticket.
     */
    public function show($id)
    {
        $ticket = Ticket::with(['user', 'assignedUser', 'company', 'comments.user'])
            ->findOrFail($id);

        return view('admin.tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit($id)
    {
        $ticket = Ticket::with(['user', 'assignedUser', 'company'])->findOrFail($id);

        return view('admin.tickets.edit', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Update the specified ticket.
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'subject' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:5000',
            'status' => 'sometimes|required|in:open,in_progress,waiting,resolved,closed',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'category' => 'sometimes|required|in:technical,billing,feature_request,bug,general',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket = $this->ticketService->updateTicket($ticket, $validated);

        return redirect()->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Ticket updated successfully.');
    }

    /**
     * Add a comment to the ticket.
     */
    public function addComment(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
            'is_internal' => 'sometimes|boolean',
        ]);

        $comment = $this->ticketService->addComment($ticket, array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        return redirect()->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Comment added successfully.');
    }

    /**
     * Assign ticket to a user.
     */
    public function assign(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket = $this->ticketService->assignTicket($ticket, $validated['assigned_to']);

        return redirect()->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Ticket assigned successfully.');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,waiting,resolved,closed',
        ]);

        $status = TicketStatus::from($validated['status']);
        $ticket = $this->ticketService->updateStatus($ticket, $status);

        return redirect()->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Ticket status updated successfully.');
    }
}
