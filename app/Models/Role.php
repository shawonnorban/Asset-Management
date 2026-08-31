<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'role', 'name', 'guard_name',
    ];

    public const LABELS = [
        'admin' => 'Super Admin',
        'super_admin' => 'Super Admin',
        'manager' => 'Management',
        'management' => 'Management',
        'department_head' => 'Department Head',
        'staff' => 'Employee',
        'employee' => 'Employee',
    ];

    public function getLabelAttribute(): string
    {
        $key = $this->name ?: $this->role;

        return self::LABELS[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    public function getRoleAttribute(): ?string
    {
        return $this->attributes['role'] ?? $this->attributes['name'] ?? null;
    }

}
