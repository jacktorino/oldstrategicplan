<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponsibleUnit extends Model
{
    public const CATEGORY_ACADEMIC_COLLEGE = 'Academic colleges';
    public const CATEGORY_ADMIN_SUPPORT = 'Administrative & support offices';
    public const CATEGORY_ACADEMIC_SUPPORT = 'Academic support units';
    public const CATEGORY_SATELLITE_CAMPUS = 'Satellite campuses';

    protected $fillable = [
        'name',
        'acronym',
        'category',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subKras()
    {
        return $this->hasMany(SubKra::class);
    }

    public function kpis()
    {
        return $this->belongsToMany(Kpi::class);
    }
}
