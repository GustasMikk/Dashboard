<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    protected $fillable = [
        'wazuh_incident_id',
        'title',
        'severity',
        'rule',
        'host',
        'status',
        'open',
        'assigned_user_id',
        'group_id',
        'users',
        'opened_at',
        'resolved_at',
        'closed_at',
        'ai_description',
        'ai_recommendations',
        'ai_root_cause',
        'raw_payload',
        'occurrences_count',
        'last_occurrence_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'opened_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_occurrence_at' => 'datetime',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function assignedIncidentGroups(): BelongsTo
    {
        return $this->belongsTo(IncidentGroup::class, 'group_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
