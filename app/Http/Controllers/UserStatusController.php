<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class UserStatusController extends Controller
{
    public function index()
    {
        $users = User::with('role')
            ->orderBy('name')
            ->get()
            ->map(function ($user) {
                $user->is_online = Cache::has('user-is-online-' . $user->id);
                return $user;
            });

        return Inertia::render('users/status', [
            'title' => 'Account Status', 'description' => 'Current online presence for system users.',
            'rows' => $users->map(fn ($user) => [
                'id' => $user->id, 'name' => $user->name, 'email' => $user->email,
                'role' => optional($user->role)->role ?? '-', 'status' => $user->is_online ? 'Online' : 'Offline',
            ])->values(),
        ]);
    }
}
