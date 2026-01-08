<?php

namespace App\Http\Services;

use App\Models\Company;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleService
{
    /**
     * Create default roles for a company
     */
    public function createDefaultRoles(int $companyId): void
    {
        $defaultRoles = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Full access to all features and settings',
                'is_system' => true,
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can manage invoices, clients, and reports',
                'is_system' => true,
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'Can create and view invoices',
                'is_system' => true,
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Read-only access',
                'is_system' => true,
            ],
        ];

        foreach ($defaultRoles as $roleData) {
            Role::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'slug' => $roleData['slug'],
                ],
                array_merge($roleData, [
                    'company_id' => $companyId,
                    'is_active' => true,
                ])
            );
        }
    }

    /**
     * Seed default permissions
     */
    public function seedDefaultPermissions(): void
    {
        $permissions = [
            // Invoices
            ['name' => 'invoices.view', 'display_name' => 'View Invoices', 'category' => 'invoices', 'sort_order' => 1],
            ['name' => 'invoices.create', 'display_name' => 'Create Invoices', 'category' => 'invoices', 'sort_order' => 2],
            ['name' => 'invoices.edit', 'display_name' => 'Edit Invoices', 'category' => 'invoices', 'sort_order' => 3],
            ['name' => 'invoices.delete', 'display_name' => 'Delete Invoices', 'category' => 'invoices', 'sort_order' => 4],
            ['name' => 'invoices.send', 'display_name' => 'Send Invoices', 'category' => 'invoices', 'sort_order' => 5],
            ['name' => 'invoices.payments', 'display_name' => 'Record Payments', 'category' => 'invoices', 'sort_order' => 6],

            // Estimates
            ['name' => 'estimates.view', 'display_name' => 'View Estimates', 'category' => 'estimates', 'sort_order' => 10],
            ['name' => 'estimates.create', 'display_name' => 'Create Estimates', 'category' => 'estimates', 'sort_order' => 11],
            ['name' => 'estimates.edit', 'display_name' => 'Edit Estimates', 'category' => 'estimates', 'sort_order' => 12],
            ['name' => 'estimates.delete', 'display_name' => 'Delete Estimates', 'category' => 'estimates', 'sort_order' => 13],
            ['name' => 'estimates.convert', 'display_name' => 'Convert to Invoice', 'category' => 'estimates', 'sort_order' => 14],

            // Clients
            ['name' => 'clients.view', 'display_name' => 'View Clients', 'category' => 'clients', 'sort_order' => 20],
            ['name' => 'clients.create', 'display_name' => 'Create Clients', 'category' => 'clients', 'sort_order' => 21],
            ['name' => 'clients.edit', 'display_name' => 'Edit Clients', 'category' => 'clients', 'sort_order' => 22],
            ['name' => 'clients.delete', 'display_name' => 'Delete Clients', 'category' => 'clients', 'sort_order' => 23],

            // Expenses
            ['name' => 'expenses.view', 'display_name' => 'View Expenses', 'category' => 'expenses', 'sort_order' => 30],
            ['name' => 'expenses.create', 'display_name' => 'Create Expenses', 'category' => 'expenses', 'sort_order' => 31],
            ['name' => 'expenses.edit', 'display_name' => 'Edit Expenses', 'category' => 'expenses', 'sort_order' => 32],
            ['name' => 'expenses.delete', 'display_name' => 'Delete Expenses', 'category' => 'expenses', 'sort_order' => 33],

            // Credit Notes
            ['name' => 'credit-notes.view', 'display_name' => 'View Credit Notes', 'category' => 'credit-notes', 'sort_order' => 40],
            ['name' => 'credit-notes.create', 'display_name' => 'Create Credit Notes', 'category' => 'credit-notes', 'sort_order' => 41],
            ['name' => 'credit-notes.edit', 'display_name' => 'Edit Credit Notes', 'category' => 'credit-notes', 'sort_order' => 42],
            ['name' => 'credit-notes.delete', 'display_name' => 'Delete Credit Notes', 'category' => 'credit-notes', 'sort_order' => 43],

            // Inventory
            ['name' => 'inventory.view', 'display_name' => 'View Inventory', 'category' => 'inventory', 'sort_order' => 50],
            ['name' => 'inventory.create', 'display_name' => 'Create Inventory Items', 'category' => 'inventory', 'sort_order' => 51],
            ['name' => 'inventory.edit', 'display_name' => 'Edit Inventory Items', 'category' => 'inventory', 'sort_order' => 52],
            ['name' => 'inventory.delete', 'display_name' => 'Delete Inventory Items', 'category' => 'inventory', 'sort_order' => 53],
            ['name' => 'inventory.purchase', 'display_name' => 'Record Stock Purchases', 'category' => 'inventory', 'sort_order' => 54],
            ['name' => 'inventory.adjust', 'display_name' => 'Adjust Stock', 'category' => 'inventory', 'sort_order' => 55],

            // Reports
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'category' => 'reports', 'sort_order' => 60],
            ['name' => 'reports.financial', 'display_name' => 'View Financial Reports', 'category' => 'reports', 'sort_order' => 61],

            // Settings
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'category' => 'settings', 'sort_order' => 70],
            ['name' => 'settings.edit', 'display_name' => 'Edit Settings', 'category' => 'settings', 'sort_order' => 71],
            ['name' => 'settings.users', 'display_name' => 'Manage Users', 'category' => 'settings', 'sort_order' => 72],
            ['name' => 'settings.roles', 'display_name' => 'Manage Roles', 'category' => 'settings', 'sort_order' => 73],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }

    /**
     * Assign default permissions to system roles
     */
    public function assignDefaultPermissions(int $companyId): void
    {
        $this->seedDefaultPermissions();

        $administrator = Role::where('company_id', $companyId)->where('slug', 'administrator')->first();
        $manager = Role::where('company_id', $companyId)->where('slug', 'manager')->first();
        $staff = Role::where('company_id', $companyId)->where('slug', 'staff')->first();
        $viewer = Role::where('company_id', $companyId)->where('slug', 'viewer')->first();

        if ($administrator) {
            // Administrator gets all permissions
            $administrator->permissions()->sync(Permission::pluck('id'));
        }

        if ($manager) {
            // Manager gets most permissions except user/role management
            $managerPermissions = Permission::whereNotIn('name', [
                'settings.users',
                'settings.roles',
            ])->pluck('id');
            $manager->permissions()->sync($managerPermissions);
        }

        if ($staff) {
            // Staff can create/view invoices, clients, estimates
            $staffPermissions = Permission::whereIn('name', [
                'invoices.view',
                'invoices.create',
                'invoices.edit',
                'invoices.send',
                'estimates.view',
                'estimates.create',
                'estimates.edit',
                'clients.view',
                'clients.create',
                'clients.edit',
                'expenses.view',
                'expenses.create',
                'expenses.edit',
                'inventory.view',
            ])->pluck('id');
            $staff->permissions()->sync($staffPermissions);
        }

        if ($viewer) {
            // Viewer gets read-only permissions
            $viewerPermissions = Permission::whereIn('name', [
                'invoices.view',
                'estimates.view',
                'clients.view',
                'expenses.view',
                'credit-notes.view',
                'inventory.view',
                'reports.view',
            ])->pluck('id');
            $viewer->permissions()->sync($viewerPermissions);
        }
    }

    /**
     * Create a new role
     */
    public function createRole(Request $request): Role
    {
        $companyId = CurrentCompanyService::requireId();

        $data = $request->only([
            'name',
            'description',
            'is_active',
        ]);

        $data['company_id'] = $companyId;
        $data['slug'] = Str::slug($data['name']);
        $data['is_system'] = false;
        $data['is_active'] = $data['is_active'] ?? true;

        $role = Role::create($data);

        // Assign permissions if provided
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->input('permissions', []));
        }

        return $role;
    }

    /**
     * Update a role
     */
    public function updateRole(Role $role, Request $request): Role
    {
        $data = $request->only([
            'name',
            'description',
            'is_active',
        ]);

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $role->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $role->update($data);

        // Update permissions if provided
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->input('permissions', []));
        }

        return $role;
    }

    /**
     * Format role for list display
     */
    public function formatRoleForList(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'is_system' => $role->is_system,
            'is_active' => $role->is_active,
            'permissions_count' => $role->permissions_count ?? $role->permissions()->count(),
            'users_count' => $role->users_count ?? $role->users()->where('user_company_roles.company_id', $role->company_id)->count(),
        ];
    }

    /**
     * Format role with full details
     */
    public function formatRoleForShow(Role $role): array
    {
        $data = $this->formatRoleForList($role);

        $data['permissions'] = $role->permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name,
                'category' => $permission->category,
            ];
        });

        $data['users'] = $role->users()
            ->where('user_company_roles.company_id', $role->company_id)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });

        return $data;
    }

    /**
     * Assign role to user for a company
     */
    public function assignRoleToUser(User $user, Role $role, int $companyId): void
    {
        $user->assignRole($role, $companyId);
    }

    /**
     * Remove role from user for a company
     */
    public function removeRoleFromUser(User $user, Role $role, int $companyId): void
    {
        $user->removeRole($role, $companyId);
    }
}
