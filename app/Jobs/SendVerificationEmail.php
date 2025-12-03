<?php

namespace App\Jobs;

use App\Mail\EmailVerificationNotification;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendVerificationEmail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public ?string $ipAddress = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Invalidate all previous unused tokens for this user
        EmailVerification::forUser($this->user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Generate new token
        $token = Str::uuid()->toString();
        $expiresAt = now()->addHours(24); // TTL = 24 hours

        // Create verification record
        $verification = EmailVerification::create([
            'user_id' => $this->user->id,
            'token' => $token,
            'expires_at' => $expiresAt,
            'ip_address' => $this->ipAddress,
        ]);

        // Send email
        try {
            Mail::to($this->user->email)->send(
                new EmailVerificationNotification($this->user, $token)
            );

            Log::info('Verification email sent', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'verification_id' => $verification->id,
            ]);

            // Increment attempts
            $verification->increment('attempts');
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'error' => $e->getMessage(),
                'verification_id' => $verification->id,
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }
}
