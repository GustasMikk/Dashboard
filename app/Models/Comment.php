<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'incident_group_id',
        'user_id',
        'comment_text',
    ];

    public function incidentGroup(): BelongsTo
    {
        return $this->belongsTo(IncidentGroup::class, 'incident_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
