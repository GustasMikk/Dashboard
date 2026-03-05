<?php

namespace App\Jobs;

use App\Mail\IncidentAnalyzedMail;
use App\Models\Incident;
use App\Services\AiService;
use App\Settings\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessWazuhAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(private array $payload) {}

    public function handle(): void
    {
        $rule     = $this->payload['rule'] ?? [];
        $agent    = $this->payload['agent'] ?? [];
        $id       = $this->payload['id'] ?? uniqid('wazuh_');
        $level    = $rule['level'] ?? 0;

        $severity = match(true) {
            $level >= 12 => 'critical',
            $level >= 8  => 'high',
            $level >= 4  => 'medium',
            default      => 'low',
        };

        $existing = Incident::where('wazuh_incident_id', $rule['id'] ?? $id)
            ->where('status', '!=', 'closed')
            ->first();

        if ($existing) {
            $existing->increment('occurrences_count');
            $existing->update(['last_occurrence_at' => now()]);
            return;
        }
        
        $incident = Incident::create([
            'wazuh_incident_id'  => $rule['id'] ?? $id,
            'title'              => $rule['description'] ?? 'Unknown Alert',
            'severity'           => $severity,
            'rule'               => $rule['id'] ?? 'N/A',
            'host'               => $agent['name'] ?? $agent['ip'] ?? 'unknown',
            'status'             => 'open',
            'opened_at'          => now(),
            'last_occurrence_at' => now(),
            'raw_payload'        => json_encode($this->payload),
            'occurrences_count' => 1,
        ]);

        $settings = app(NotificationSettings::class);

        if ($settings->ai_generation_enabled && in_array($incident->severity, $settings->ai_severities)) {
            try {
                $ai = app(AiService::class);
                $result = $ai->analyzeIncident($incident->toArray());
                $incident->update($result);
                $incident->refresh();

                if ($settings->email_enabled && in_array($incident->severity, $settings->email_severities)) {
                    Mail::to(config('mail.admin_address'))->queue(new IncidentAnalyzedMail($incident));
                }
            } catch (\Exception $e) {
                Log::error('AI analysis failed', ['incident_id' => $incident->id, 'error' => $e->getMessage()]);
            }
        }
    }
}