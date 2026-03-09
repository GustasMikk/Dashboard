<?php

namespace App\Jobs;

use App\Mail\IncidentAnalyzedMail;
use App\Models\Incident;
use App\Models\IncidentGroup;
use App\Services\AiService;
use App\Settings\NotificationSettings;
use Illuminate\Support\Facades\DB;
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
        Log::info('Job started');

        $rule       = $this->payload['rule'] ?? [];
        $agent      = $this->payload['agent'] ?? [];
        $mitre_id   = data_get($this->payload, 'rule.mitre.id.0');
        $mitre_base = $mitre_id ? explode('.', $mitre_id)[0] : null;
        $id         = $this->payload['id'] ?? uniqid('wazuh_');
        $level      = $rule['level'] ?? 0;
        $host       = $agent['name'] ?? $agent['ip'] ?? 'unknown';
        $mitre_tactic = data_get($this->payload, 'rule.mitre.tactic.0', 'Unknown');
        $title = "{$mitre_tactic} on {$host} at " . now()->format('Y-m-d H:i');

        $severity = match(true) {
            $level >= 12 => 'critical',
            $level >= 8  => 'high',
            $level >= 4  => 'medium',
            default      => 'low',
        };

        $existing = Incident::where('rule', $rule['id'] ?? 'N/A')
            ->where('host', $host)
            ->where('last_occurrence_at', '>=', now()->subMinutes(15))
            ->first();

        if ($existing) {
            $existing->increment('occurrences_count');
            $existing->update(['last_occurrence_at' => now()]);

            if ($existing->incident_group_id) {
                IncidentGroup::where('id', $existing->incident_group_id)->increment('total_occurrences');
                IncidentGroup::where('id', $existing->incident_group_id)->update(['last_occurrence_at' => now()]);
            }
            return;
        }

        $incidentGroup = null;
        if ($mitre_base) {
            $incidentGroup = IncidentGroup::where('mitre_id', $mitre_base)
                ->where('host', $host)
                ->where('status', 'open')
                ->first();

            if (!$incidentGroup) {
                $incidentGroup = IncidentGroup::create([
                    'title' => $mitre_base ? $title : ($rule['description'] ?? 'Unknown Alert'),
                    'mitre_id'           => $mitre_base,
                    'highest_severity'   => $severity,
                    'host'               => $host,
                    'opened_at'          => now(),
                    'last_occurrence_at' => now(),
                    'total_occurrences'  => 0,
                    'status'             => 'open',
                ]);
            }

            $incidentGroup->increment('total_occurrences');
        }

        $incident = Incident::create([
            'wazuh_incident_id'   => $id,
            'incident_group_id'   => $incidentGroup?->id,
            'mitre_id'            => $mitre_base,
            'title'               => $rule['description'] ?? 'Unknown Alert',
            'severity'            => $severity,
            'rule'                => $rule['id'] ?? 'N/A',
            'host'                => $host,
            'first_occurrence_at' => now(),
            'last_occurrence_at'  => now(),
            'raw_payload'         => json_encode($this->payload),
            'occurrences_count'   => 1,
        ]);

        $settings = app(NotificationSettings::class);

        if ($settings->ai_generation_enabled && in_array($incident->severity, $settings->ai_severities)) {
            try {
                if ($incidentGroup) {
                    DB::transaction(function () use ($incidentGroup) {
                        $incidentGroup = IncidentGroup::where('id', $incidentGroup->id)->lockForUpdate()->first();
                        
                        if (!$incidentGroup->ai_scheduled_at || $incidentGroup->ai_scheduled_at < now()) {
                            dispatch(new AnalyzeIncidentGroupJob($incidentGroup))
                                ->delay(now()->addMinute());
                            $incidentGroup->update(['ai_scheduled_at' => now()->addMinute()]);
                        } else {
                            $incidentGroup->update(['ai_scheduled_at' => now()->addMinute()]);
                        }
                    });
                } else {
                    // No group, analyze individual incident immediately
                    // $ai = app(AiService::class);
                    // $result = $ai->analyzeIncident($incident);
                    // $incident->update($result);
                    // $incident->refresh();

                    // if ($settings->email_enabled && in_array($incident->severity, $settings->email_severities)) {
                    //     Mail::to(config('mail.admin_address'))->queue(new IncidentAnalyzedMail($incident));
                    // }
                }
            } catch (\Exception $e) {
                Log::error('AI analysis failed', ['incident_id' => $incident->id, 'error' => $e->getMessage()]);
            }
        }
    }
}