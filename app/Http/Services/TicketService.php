<?php

namespace App\Http\Services;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\TicketStatus;

class TicketService
{
    /**
     * Create a new ticket.
     *
     * @param  array<string, mixed>  $data
     */
    public function createTicket(array $data): Ticket
    {
        return Ticket::create([
            'company_id' => $data['company_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'status' => $data['status'] ?? TicketStatus::Open->value,
            'priority' => $data['priority'] ?? 'medium',
            'category' => $data['category'] ?? 'general',
            'assigned_to' => $data['assigned_to'] ?? null,
        ]);
    }

    /**
     * Update a ticket.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTicket(Ticket $ticket, array $data): Ticket
    {
        $updateData = [];

        if (isset($data['subject'])) {
            $updateData['subject'] = $data['subject'];
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
            // Set resolved_at or closed_at based on status
            if ($data['status'] === TicketStatus::Resolved->value && ! $ticket->resolved_at) {
                $updateData['resolved_at'] = now();
            }
            if ($data['status'] === TicketStatus::Closed->value && ! $ticket->closed_at) {
                $updateData['closed_at'] = now();
            }
        }
        if (isset($data['priority'])) {
            $updateData['priority'] = $data['priority'];
        }
        if (isset($data['category'])) {
            $updateData['category'] = $data['category'];
        }
        if (isset($data['assigned_to'])) {
            $updateData['assigned_to'] = $data['assigned_to'];
        }

        $ticket->update($updateData);

        return $ticket->fresh();
    }

    /**
     * Add a comment to a ticket.
     *
     * @param  array<string, mixed>  $data
     */
    public function addComment(Ticket $ticket, array $data): TicketComment
    {
        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $data['user_id'] ?? auth()->id(),
            'comment' => $data['comment'],
            'is_internal' => $data['is_internal'] ?? false,
        ]);

        // If ticket is resolved/closed and a new comment is added, reopen it
        if (in_array($ticket->status, [TicketStatus::Resolved->value, TicketStatus::Closed->value])) {
            $ticket->update([
                'status' => TicketStatus::Open->value,
                'resolved_at' => null,
                'closed_at' => null,
            ]);
        }

        return $comment;
    }

    /**
     * Assign ticket to a user.
     */
    public function assignTicket(Ticket $ticket, int $userId): Ticket
    {
        $ticket->update(['assigned_to' => $userId]);

        return $ticket->fresh();
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Ticket $ticket, TicketStatus $status): Ticket
    {
        $updateData = ['status' => $status->value];

        if ($status === TicketStatus::Resolved && ! $ticket->resolved_at) {
            $updateData['resolved_at'] = now();
        }
        if ($status === TicketStatus::Closed && ! $ticket->closed_at) {
            $updateData['closed_at'] = now();
        }

        $ticket->update($updateData);

        return $ticket->fresh();
    }
}
