<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $fillable = [
        'wazuh_incident_id',
        'mitre_id',
        'title',
        'severity',
        'rule',
        'host',
        'incident_group_id',
        'raw_payload',
        'first_occurrence_at',
        'occurrences_count',
        'last_occurrence_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'first_occurrence' => 'datetime',
        'last_occurrence_at' => 'datetime',
    ];

    public function incidentGroup(): BelongsTo
    {
        return $this->belongsTo(IncidentGroup::class, 'incident_group_id');
    }
}
