<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncidentGroup extends Model
{
    protected $fillable = [
        'title',
        'mitre_id',
        'total_occurrences',
        'last_occurrence_at',
        'highest_severity',
        'status',
        'host',
        'assigned_user_id',
        'opened_at',
        'resolved_at',
        'closed_at',
        'ai_description',
        'ai_recommendations',
        'ai_root_cause',
        'ai_scheduled_at',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'incident_group_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'incident_group_id');
    }
}
