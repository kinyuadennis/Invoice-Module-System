<?php

namespace App\Http\Services;

use App\Models\Client;
use App\Models\ClientActivity;

class ClientActivityService
{
    /**
     * Log an activity for a client.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function logActivity(Client $client, string $type, string $description, ?array $metadata = null, ?int $userId = null): ClientActivity
    {
        return ClientActivity::create([
            'client_id' => $client->id,
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log client creation.
     */
    public function logCreated(Client $client, ?int $userId = null): ClientActivity
    {
        return $this->logActivity($client, 'created', "Client '{$client->name}' was created.", null, $userId);
    }

    /**
     * Log client update.
     */
    public function logUpdated(Client $client, array $changes, ?int $userId = null): ClientActivity
    {
        $changedFields = implode(', ', array_keys($changes));

        return $this->logActivity($client, 'updated', "Client '{$client->name}' was updated. Changed fields: {$changedFields}.", $changes, $userId);
    }

    /**
     * Log invoice creation for client.
     */
    public function logInvoiceCreated(Client $client, int $invoiceId, string $invoiceNumber, ?int $userId = null): ClientActivity
    {
        return $this->logActivity($client, 'invoice_created', "Invoice #{$invoiceNumber} was created for this client.", [
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoiceNumber,
        ], $userId);
    }

    /**
     * Log payment received for client.
     */
    public function logPaymentReceived(Client $client, int $paymentId, float $amount, string $invoiceNumber, ?int $userId = null): ClientActivity
    {
        return $this->logActivity($client, 'payment_received', 'Payment of '.number_format($amount, 2)." received for invoice #{$invoiceNumber}.", [
            'payment_id' => $paymentId,
            'amount' => $amount,
            'invoice_number' => $invoiceNumber,
        ], $userId);
    }

    /**
     * Log note added to client.
     */
    public function logNoteAdded(Client $client, int $noteId, ?int $userId = null): ClientActivity
    {
        return $this->logActivity($client, 'note_added', 'A note was added to this client.', [
            'note_id' => $noteId,
        ], $userId);
    }

    /**
     * Log estimate creation for client.
     */
    public function logEstimateCreated(Client $client, int $estimateId, string $estimateNumber, ?int $userId = null): ClientActivity
    {
        return $this->logActivity($client, 'estimate_created', "Estimate #{$estimateNumber} was created for this client.", [
            'estimate_id' => $estimateId,
            'estimate_number' => $estimateNumber,
        ], $userId);
    }

    /**
     * Log estimate sent to client.
     */
    public function logEstimateSent(Client $client, int $estimateId, string $estimateNumber, ?int $userId = null): ClientActivity
    {
        return $this->logActivity($client, 'estimate_sent', "Estimate #{$estimateNumber} was sent to this client.", [
            'estimate_id' => $estimateId,
            'estimate_number' => $estimateNumber,
        ], $userId);
    }

    /**
     * Log estimate converted to invoice.
     */
    public function logEstimateConverted(Client $client, int $estimateId, string $estimateNumber, int $invoiceId, string $invoiceNumber, ?int $userId = null): ClientActivity
    {
        return $this->logActivity($client, 'estimate_converted', "Estimate #{$estimateNumber} was converted to invoice #{$invoiceNumber}.", [
            'estimate_id' => $estimateId,
            'estimate_number' => $estimateNumber,
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoiceNumber,
        ], $userId);
    }
}
