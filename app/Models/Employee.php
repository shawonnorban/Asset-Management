<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'employee_code',
        'name',
        'image',
        'user_id',
        'department_id',
        'position_id',
        'location_id',
        'father_name',
        'mother_name',
        'nid_number',
        'present_address',
        'permanent_address',
        'mail_address',
        'mobile',
        'join_date',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    /**
     * RELATIONS
     */

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    /** Site the employee works at (NCL, NFL, ...) */
    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    /**
     * Relation: one employee can hold many assets
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'employee_id');
    }

    /**
     * Relation: every asset handover this employee was part of
     */
    public function assetAssignments()
    {
        return $this->hasMany(AssetAssignment::class, 'employee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * HELPERS
     */

    public function getDepartmentNameAttribute(): string
    {
        return optional($this->department)->name ?? '-';
    }

    public function getPositionNameAttribute(): string
    {
        return optional($this->position)->name ?? '-';
    }
}
