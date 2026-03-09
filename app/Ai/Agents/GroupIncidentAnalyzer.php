<?php

namespace App\Ai\Agents;

use App\Models\IncidentGroup;
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
        return 'You are an expert cybersecurity analyst. Analyze groups of related Wazuh SIEM incidents sharing the same MITRE ATT&CK technique. Identify the overall attack pattern, assess combined risk, and provide strategic remediation advice. Be specific and use the actual data provided.';
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
        $incidents = $this->group->incidents->map(fn ($i) =>
            "- [{$i->severity}] {$i->title} on {$i->host} ({$i->occurrences_count}x)"
        )->join("\n");

        return <<<PROMPT
        Analyze this group of related security incidents:

        Group Title: {$this->group->title}
        MITRE ATT&CK Technique: {$this->group->mitre_id}
        Host: {$this->group->host}
        Total Occurrences: {$this->group->total_occurrences}
        Status: {$this->group->status}
        Time Range: {$this->group->opened_at} to {$this->group->last_occurrence_at}

        Individual Incidents:
        {$incidents}
        PROMPT;
    }
}