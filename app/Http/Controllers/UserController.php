<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display the user list
     */
    public function index()
    {
        return Inertia::render('users/index', [
            'title' => 'Account List', 'description' => 'Manage system accounts and access roles.',
            'rows' => User::with(['role', 'employee'])->orderByDesc('id')->get()->map(fn ($user) => [
                'id' => $user->id, 'name' => $user->name, 'email' => $user->email,
                'role' => $user->role_label,
                'employee' => $user->employee?->name,
                'image_url' => $user->image ? Storage::url($user->image) : ($user->employee?->image ? Storage::url($user->employee->image) : null),
                'role_key' => $user->canonicalRole(),
                'protected' => $user->isSuperAdmin(),
            ])->values(), 'canManage' => auth()->user()->can('users.manage'),
        ]);
    }

    /**
     * Show the create user form
     */
    public function create()
    {
        return Inertia::render('users/form', [
            'title' => 'Add account', 'record' => null,
            'roles' => $this->roleOptions(),
        ]);
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100', 'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|min:6|confirmed', 'image' => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
        ]);
        $image = $request->file('image')?->store('user-photos', 'public');
        $user = User::create([
            'name' => $validated['name'], 'email' => $validated['email'], 'role_id' => $validated['role_id'],
            'image' => $image, 'password' => Hash::make($validated['password']),
        ]);
        $user->syncRoles([Role::findOrFail($request->role_id)]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Show the edit user form
     */
    public function edit($id)
    {
        return Inertia::render('users/form', [
            'title' => 'Edit account', 'record' => User::findOrFail($id)->only(['id', 'name', 'email', 'role_id', 'image']),
            'roles' => $this->roleOptions(),
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role_id' => 'required|exists:roles,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:4096',
            'password'=> 'nullable|min:6|confirmed',
        ]);

        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'role_id' => $request->role_id,
        ];

        // only change the password when one was provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        if ($request->hasFile('image')) {
            if ($user->image) Storage::disk('public')->delete($user->image);
            $data['image'] = $request->file('image')->store('user-photos', 'public');
        }

        $user->update($data);
        $user->syncRoles([Role::findOrFail($request->role_id)]);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully');
    }

    private function roleOptions()
    {
        return Role::orderBy('name')->get()->map(fn ($role) => ['id' => $role->id, 'role' => $role->label])->values();
    }

    /**
     * Delete a user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->isSuperAdmin()) {
            return back()->with('error', 'The Super Admin account cannot be deleted.');
        }

        // optional: prevent deleting your own account
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully');
    }
}
