<?php

namespace App\Ai\Agents;

use App\Models\IncidentGroup;
use App\Settings\AppSettings;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class GroupIncidentAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(private IncidentGroup $group) {}

    public function instructions(): Stringable|string
    {
        return app(AppSettings::class)->ai_instructions;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ai_description' => $schema->string()->required(),
            'ai_root_cause' => $schema->string()->required(),
            'ai_recommendations' => $schema->string()->required(),
            'send_email' => $schema->boolean()->required(),
        ];
    }

    public function buildPrompt(): string
    {
        $incidents = $this->group->incidents->map(function ($i) {

            $data = json_decode($i->raw_payload, true);
            $fullLog = $data['full_log'] ?? 'No log available';

            return implode("\n", [
                "  Title: {$i->title}",
                "  Rule: {$i->rule}",
                "  Severity: {$i->severity}",
                "  Occurrences: {$i->occurrences_count}",
                "  Raw Log: {$fullLog}",
            ]);

        })->join("\n\n---\n\n");

        return <<<PROMPT
        Analyze this group of related security incidents:

        Group Title: {$this->group->title}
        MITRE ATT&CK Technique: {$this->group->mitre_id}
        Host: {$this->group->host}
        Time Range: {$this->group->opened_at} to {$this->group->last_occurrence_at}

        Individual Incidents:
        {$incidents}
        PROMPT;
    }
}
