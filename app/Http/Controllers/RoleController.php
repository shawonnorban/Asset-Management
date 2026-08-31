<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        return inertia('roles/index', [
            'title' => 'Role Management',
            'description' => 'Create roles and define their system permissions.',
            'roles' => Role::with('permissions')->orderBy('name')->get()->map(fn ($role) => [
                'id' => $role->id,
                'key' => $role->name,
                'label' => $role->label,
                'protected' => $role->name === 'super_admin',
                'permissions' => $role->permissions->pluck('name')->values(),
            ])->values(),
            'permissionOptions' => Permission::where('name', '!=', '*')->orderBy('name')->pluck('name')->values(),
            'permissionCatalog' => User::PERMISSION_CATALOG,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'role' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', "Role '{$role->name}' created successfully.");
    }

    public function update(Request $request, Role $role)
    {
        $this->ensureEditable($role);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role->update(['name' => $validated['name'], 'role' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', "Role '{$role->name}' updated successfully.");
    }

    public function destroy(Role $role)
    {
        $this->ensureEditable($role);

        if ($role->users()->exists()) {
            return back()->with('error', 'This role cannot be deleted while it is assigned to users.');
        }

        $role->delete();

        return back()->with('success', "Role '{$role->name}' deleted successfully.");
    }

    private function ensureEditable(Role $role): void
    {
        if ($role->name === 'super_admin') {
            abort(403, 'The Super Admin role is protected.');
        }
    }
}
