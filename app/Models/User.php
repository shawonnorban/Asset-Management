<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'employee_id',
        'image',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    public const ROLE_ALIASES = [
        'admin' => 'super_admin',
        'super_admin' => 'super_admin',
        'manager' => 'management',
        'management' => 'management',
        'department_head' => 'department_head',
        'staff' => 'employee',
        'employee' => 'employee',
    ];

    public const PERMISSIONS = [
        'super_admin' => ['*', 'users.view', 'users.manage', 'roles.view', 'roles.manage', 'audit.view', 'audit.manage', 'maintenance.view', 'maintenance.manage', 'assets.export', 'reports.review', 'reports.complete', 'reports.manage', 'depreciation.dispose', 'assignments.view', 'assignments.assign', 'assignments.return', 'assignments.manage', 'software_licenses.view', 'software_licenses.manage', 'assignment_audits.view', 'assignment_audits.create', 'assignment_audits.verify', 'assignment_audits.complete', 'assignment_audits.manage'],
        'management' => ['dashboard.view', 'assets.view', 'assets.manage', 'employees.view', 'employees.manage', 'master_data.manage', 'stock.view', 'licenses.manage', 'software_licenses.view', 'software_licenses.manage', 'depreciation.view', 'depreciation.manage', 'categories.view', 'categories.create', 'categories.update', 'categories.delete', 'categories.manage', 'locations.view', 'locations.create', 'locations.update', 'locations.delete', 'locations.manage', 'departments.view', 'departments.manage', 'positions.view', 'positions.manage', 'reports.view', 'reports.manage', 'reports.review', 'reports.complete', 'maintenance.view', 'maintenance.create', 'maintenance.update', 'maintenance.delete', 'maintenance.manage', 'transfers.view', 'transfers.create', 'transfers.update', 'transfers.delete', 'transfers.manage', 'disposals.view', 'disposals.create', 'disposals.update', 'disposals.delete', 'disposals.manage', 'notifications.view', 'notifications.create', 'notifications.update', 'notifications.delete', 'notifications.manage', 'assets.export', 'depreciation.dispose', 'assignments.view', 'assignments.assign', 'assignments.return', 'assignments.manage', 'assignment_audits.view', 'assignment_audits.create', 'assignment_audits.verify', 'assignment_audits.complete', 'assignment_audits.manage', 'users.view', 'users.manage', 'roles.view', 'roles.manage', 'audit.view', 'audit.manage'],
        'department_head' => ['dashboard.view', 'assets.view', 'employees.view', 'stock.view', 'categories.view', 'locations.view', 'reports.view', 'reports.manage', 'reports.review', 'reports.complete', 'maintenance.view', 'assignments.view'],
        'employee' => ['dashboard.view', 'assets.view', 'reports.create', 'reports.view_own'],
    ];

    public const PERMISSION_CATALOG = [
        'dashboard' => ['view'],
        'assets' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'stock' => ['create', 'edit', 'update', 'view', 'delete'],
        'maintenance' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'transfers' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'disposals' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'notifications' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'categories' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'locations' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'employees' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'departments' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'positions' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'assignments' => ['create', 'edit', 'update', 'view', 'delete', 'assign', 'return', 'manage'],
        'software_licenses' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'depreciation' => ['create', 'edit', 'update', 'view', 'delete', 'dispose', 'manage'],
        'reports' => ['create', 'edit', 'update', 'view', 'delete', 'review', 'complete', 'manage'],
        'users' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'roles' => ['create', 'edit', 'update', 'view', 'delete', 'manage'],
        'audit' => ['view', 'delete', 'manage'],
        'assignment_audits' => ['create', 'view', 'verify', 'complete', 'manage'],
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function lifecycleLogs()
    {
        return $this->hasMany(AssetLifecycleLog::class, 'user_id');
    }

    // =========================
    //     ROLE HELPERS
    // =========================

    /** check a single role */
    public function hasRole(string $role): bool
    {
        $expected = self::ROLE_ALIASES[$role] ?? $role;

        return $this->roles()->whereIn('name', array_keys(array_filter(self::ROLE_ALIASES, fn ($value) => $value === $expected)) + [$expected])->exists();
    }

    /** check role against a list */
    public function inRoles(array $roles): bool
    {
        return collect($roles)->contains(fn ($role) => $this->hasRole($role));
    }

    public function canonicalRole(): ?string
    {
        $role = $this->roles()->value('name') ?: optional($this->role)->role;

        return $role ? (self::ROLE_ALIASES[$role] ?? $role) : null;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->isSuperAdmin() || $this->hasPermissionTo($permission);
    }

    /** shortcut: user->role_name */
    public function getRoleNameAttribute()
    {
        return optional($this->role)->label;
    }

    public function getRoleLabelAttribute(): string
    {
        return optional($this->role)->label ?? 'Unassigned';
    }

    public function isSuperAdmin(): bool
    {
        return $this->canonicalRole() === 'super_admin';
    }
}
