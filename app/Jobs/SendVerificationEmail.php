<?php

namespace App\Jobs;

use App\Mail\EmailVerificationNotification;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendVerificationEmail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

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
        Mail::to($this->user->email)->send(
            new EmailVerificationNotification($this->user, $token)
        );

        // Increment attempts
        $verification->increment('attempts');
    }
}
