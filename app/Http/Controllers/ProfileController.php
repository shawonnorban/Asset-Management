<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return Inertia::render('profile/show', [
            'title' => 'My profile',
            'record' => $this->profileData($user),
        ]);
    }

    public function edit(Request $request)
    {
        $user = $request->user();

        return Inertia::render('profile/edit', [
            'title' => 'My profile',
            'record' => $this->profileData($user),
        ]);
    }

    private function profileData($user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role_label,
            'image_url' => $user->image ? Storage::url($user->image) : ($user->employee?->image ? Storage::url($user->employee->image) : null),
        ];
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:4096'],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        if (!empty($validated['password']) && (empty($validated['current_password']) || !Hash::check($validated['current_password'], $user->password))) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->hasFile('image')) {
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }
            $user->image = $request->file('image')->store('user-photos', 'public');
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}
