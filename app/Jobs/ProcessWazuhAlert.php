<?php

namespace App\Jobs;

use App\Models\Incident;
use App\Models\IncidentGroup;
use App\Settings\NotificationSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWazuhAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(private array $payload) {}

    public function handle(): void
    {
        Log::info('Job started');

        $rule = $this->payload['rule'] ?? [];
        $agent = $this->payload['agent'] ?? [];
        $mitre_id = data_get($this->payload, 'rule.mitre.id.0');
        $mitre_base = $mitre_id ? explode('.', $mitre_id)[0] : null;
        $id = $this->payload['id'] ?? uniqid('wazuh_');
        $level = $rule['level'] ?? 0;
        $host = $agent['name'] ?? $agent['ip'] ?? 'unknown';
        $mitre_tactic = data_get($this->payload, 'rule.mitre.tactic.0', 'Unknown');
        $title = "{$mitre_tactic} on {$host} at ".now()->format('Y-m-d H:i');

        $settings = app(NotificationSettings::class);

        $severity = match (true) {
            $level >= 12 => 'critical',
            $level >= 8 => 'high',
            $level >= 4 => 'medium',
            default => 'low',
        };

        $existing = Incident::where('rule', $rule['id'] ?? 'N/A')
            ->where('host', $host)
            ->where('first_occurrence_at', '>=', now()->subMinutes((int) $settings->time_for_new_group))
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
        $severityChanged = false;
        if ($mitre_base) {
            $incidentGroup = IncidentGroup::where('mitre_id', $mitre_base)
                ->where('host', $host)
                ->where('status', 'open')
                ->where('opened_at', '>=', now()->subMinutes((int) $settings->time_for_new_group))
                ->first();

            if (! $incidentGroup) {
                $incidentGroup = IncidentGroup::create([
                    'title' => $mitre_base ? $title : ($rule['description'] ?? 'Unknown Alert'),
                    'mitre_id' => $mitre_base,
                    'highest_severity' => $severity,
                    'host' => $host,
                    'opened_at' => now(),
                    'last_occurrence_at' => now(),
                    'total_occurrences' => 0,
                    'status' => 'open',
                ]);
            }

            if ($incidentGroup) {
                $severityOrder = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];

                $currentHighest = $severityOrder[$incidentGroup->highest_severity] ?? 0;
                $newSeverity = $severityOrder[$severity] ?? 0;

                if ($newSeverity > $currentHighest) {
                    $incidentGroup->update(['highest_severity' => $severity]);
                    $incidentGroup->refresh();
                    $severityChanged = true;
                }
            }

            $incidentGroup->increment('total_occurrences');
        } elseif (in_array($severity, $settings->ai_severities)) {
            $incidentGroup = IncidentGroup::create([
                'title' => $title,
                'mitre_id' => null,
                'highest_severity' => $severity,
                'host' => $host,
                'opened_at' => now(),
                'last_occurrence_at' => now(),
                'total_occurrences' => 0,
                'status' => 'open',
            ]);
        }

        $incident = Incident::create([
            'wazuh_incident_id' => $id,
            'incident_group_id' => $incidentGroup?->id,
            'mitre_id' => $mitre_base,
            'title' => $rule['description'] ?? 'Unknown Alert',
            'severity' => $severity,
            'rule' => $rule['id'] ?? 'N/A',
            'host' => $host,
            'first_occurrence_at' => now(),
            'last_occurrence_at' => now(),
            'raw_payload' => json_encode($this->payload),
            'occurrences_count' => 1,
        ]);

        if ($settings->ai_generation_enabled) {
            try {
                if ($incidentGroup) {
                    DB::transaction(function () use ($incidentGroup, $settings, $severityChanged) {
                        $incidentGroup = IncidentGroup::where('id', $incidentGroup->id)->lockForUpdate()->first();
                        $shouldSchedule = ! $incidentGroup->ai_scheduled_at || ($severityChanged && $incidentGroup->ai_scheduled_at < now());

                        if ($shouldSchedule && in_array($incidentGroup->highest_severity, $settings->ai_severities)) {
                            Log::info('Scheduled');
                            dispatch(new AnalyzeIncidentGroupJob($incidentGroup))
                                ->delay(now()->addMinutes((int) $settings->time_for_new_group));
                            $incidentGroup->update(['ai_scheduled_at' => now()->addMinutes((int) $settings->time_to_generate_ai_solution)]);
                        } else {
                            // For pushing AI generation forward
                            // $incidentGroup->update(['ai_scheduled_at' => now()->addMinutes((int) $settings->time_for_new_group)]);
                        }
                    });
                }
            } catch (\Exception $e) {
                Log::error('AI analysis failed', ['incident_id' => $incident->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
