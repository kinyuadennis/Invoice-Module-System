<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Services\RoleService;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\CurrentCompanyService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index()
    {
        $companyId = CurrentCompanyService::requireId();

        $roles = Role::where('company_id', $companyId)
            ->withCount(['permissions', 'users' => function ($query) use ($companyId) {
                $query->where('user_company_roles.company_id', $companyId);
            }])
            ->latest()
            ->get()
            ->map(function (Role $role) {
                return $this->roleService->formatRoleForList($role);
            });

        return view('user.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $companyId = CurrentCompanyService::requireId();

        $permissions = Permission::orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        return view('user.roles.create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $companyId = CurrentCompanyService::requireId();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = $this->roleService->createRole($request);

        return redirect()->route('user.roles.show', $role->id)
            ->with('success', 'Role created successfully.');
    }

    public function show($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $role = Role::where('company_id', $companyId)
            ->with(['permissions', 'users' => function ($query) use ($companyId) {
                $query->where('user_company_roles.company_id', $companyId);
            }])
            ->findOrFail($id);

        $roleData = $this->roleService->formatRoleForShow($role);

        // Convert permissions collection to grouped array for view
        $roleData['permissions'] = collect($roleData['permissions'])->groupBy('category');

        return view('user.roles.show', [
            'role' => $roleData,
        ]);
    }

    public function edit($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $role = Role::where('company_id', $companyId)
            ->with('permissions')
            ->findOrFail($id);

        if ($role->is_system) {
            return redirect()->route('user.roles.show', $role->id)
                ->with('error', 'System roles cannot be edited.');
        }

        $permissions = Permission::orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        $roleData = $this->roleService->formatRoleForShow($role);

        // Convert permissions collection to grouped array for view (consistent with show method)
        $roleData['permissions'] = collect($roleData['permissions'])->groupBy('category');

        return view('user.roles.edit', [
            'role' => $roleData,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $role = Role::where('company_id', $companyId)
            ->findOrFail($id);

        if ($role->is_system) {
            return back()->withErrors([
                'message' => 'System roles cannot be edited.',
            ]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $this->roleService->updateRole($role, $request);

        return redirect()->route('user.roles.show', $role->id)
            ->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $companyId = CurrentCompanyService::requireId();

        $role = Role::where('company_id', $companyId)
            ->findOrFail($id);

        if (! $role->canBeDeleted()) {
            return back()->withErrors([
                'message' => 'System roles cannot be deleted.',
            ]);
        }

        $role->delete();

        return redirect()->route('user.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Assign role to user
     */
    public function assignToUser(Request $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $role = Role::where('company_id', $companyId)
            ->findOrFail($id);

        $user = User::findOrFail($request->input('user_id'));

        $this->roleService->assignRoleToUser($user, $role, $companyId);

        return back()->with('success', 'Role assigned to user successfully.');
    }

    /**
     * Remove role from user
     */
    public function removeFromUser(Request $request, $id)
    {
        $companyId = CurrentCompanyService::requireId();

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $role = Role::where('company_id', $companyId)
            ->findOrFail($id);

        $user = User::findOrFail($request->input('user_id'));

        $this->roleService->removeRoleFromUser($user, $role, $companyId);

        return back()->with('success', 'Role removed from user successfully.');
    }
}
