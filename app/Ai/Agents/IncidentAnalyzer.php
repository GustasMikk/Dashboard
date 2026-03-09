<?php

namespace App\Ai\Agents;

use App\Models\Incident;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use App\Settings\NotificationSettings;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class IncidentAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(private Incident $incident) {}

    public function instructions(): Stringable|string
    {
        return 'You are an expert cybersecurity analyst. Analyze Wazuh SIEM incidents and provide clear, actionable insights. Be specific, use the actual data provided, never make things up.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'ai_description'     => $schema->string()->required(),
            'ai_root_cause'      => $schema->string()->required(),
            'ai_recommendations' => $schema->string()->required(),
        ];
    }

    public function buildPrompt(): string
    {
        return <<<PROMPT
        Analyze this security incident:

        Title: {$this->incident->title}
        Severity: {$this->incident->severity}
        Rule ID: {$this->incident->rule}
        Host: {$this->incident->host}
        MITRE ATT&CK: {$this->incident->mitre_id}
        Occurrences: {$this->incident->occurrences_count}
        First Seen: {$this->incident->first_occurrence_at}
        Last Seen: {$this->incident->last_occurrence_at}
        PROMPT;
    }
}
