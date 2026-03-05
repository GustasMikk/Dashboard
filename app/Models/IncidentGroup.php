<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentGroup extends Model
{
    protected $fillable = [
        'name',
        'mitre_id',
        'last_occurrence',
        'severity',
    ];
}
