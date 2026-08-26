<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $table = 'positions';

    protected $fillable = [
        'name',
    ];

    /**
     * RELATION
     * One position is held by many employees
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }
}
