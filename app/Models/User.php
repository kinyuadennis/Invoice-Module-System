<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_id',
        'active_company_id',
        'onboarding_completed',

        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed' => 'boolean',
        ];
    }

    /**
     * The company this user belongs to (legacy - for backward compatibility).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The currently active/selected company for this user.
     */
    public function activeCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'active_company_id');
    }

    /**
     * All companies owned by this user.
     */
    public function ownedCompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'owner_user_id');
    }

    /**
     * Get the current active company, falling back to first owned company or legacy company_id.
     */
    public function getCurrentCompany(): ?Company
    {
        // First try active company
        if ($this->active_company_id && $this->activeCompany) {
            return $this->activeCompany;
        }

        // Fallback to first owned company
        $ownedCompany = $this->ownedCompanies()->first();
        if ($ownedCompany) {
            return $ownedCompany;
        }

        // Legacy fallback to company_id
        return $this->company;
    }

    /**
     * Clients created by this user (scoped to company).
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Invoices created by this user (scoped to company).
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Email verifications for this user (legacy - kept for audit if needed).
     */
    public function emailVerifications(): HasMany
    {
        return $this->hasMany(EmailVerification::class);
    }

    /**
     * Get the email address that should be used for verification.
     */
    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail);
    }
}
