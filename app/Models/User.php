<?php

namespace App\Models;

use App\Notifications\VerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /**
     * Get all roles for this user across all companies.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_company_roles')
            ->withPivot('company_id')
            ->withTimestamps();
    }

    /**
     * Get roles for a specific company.
     */
    public function rolesForCompany(int $companyId): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_company_roles')
            ->wherePivot('company_id', $companyId)
            ->withPivot('company_id')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role in a company.
     */
    public function hasRole(string $roleSlug, ?int $companyId = null): bool
    {
        $companyId = $companyId ?? $this->active_company_id ?? $this->company_id;

        if (! $companyId) {
            return false;
        }

        return $this->rolesForCompany($companyId)
            ->where('slug', $roleSlug)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Check if user has a specific permission in a company.
     */
    public function hasPermission(string $permission, ?int $companyId = null): bool
    {
        $companyId = $companyId ?? $this->active_company_id ?? $this->company_id;

        if (! $companyId) {
            return false;
        }

        // Check if user is company owner (has all permissions)
        $company = Company::find($companyId);
        if ($company && $company->owner_user_id === $this->id) {
            return true;
        }

        // Check through roles
        return $this->rolesForCompany($companyId)
            ->where('is_active', true)
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    /**
     * Get all permissions for user in a company.
     */
    public function getPermissions(?int $companyId = null): array
    {
        $companyId = $companyId ?? $this->active_company_id ?? $this->company_id;

        if (! $companyId) {
            return [];
        }

        // Check if user is company owner (has all permissions)
        $company = Company::find($companyId);
        if ($company && $company->owner_user_id === $this->id) {
            return Permission::pluck('name')->toArray();
        }

        // Get permissions through roles
        return $this->rolesForCompany($companyId)
            ->where('is_active', true)
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions->pluck('name');
            })
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Assign a role to user for a company.
     */
    public function assignRole(Role $role, int $companyId): void
    {
        $this->roles()->syncWithoutDetaching([
            $role->id => ['company_id' => $companyId],
        ]);
    }

    /**
     * Remove a role from user for a company.
     */
    public function removeRole(Role $role, int $companyId): void
    {
        $this->roles()->wherePivot('company_id', $companyId)->detach($role->id);
    }
}
