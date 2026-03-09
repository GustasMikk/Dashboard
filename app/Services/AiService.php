<?php

namespace App\Services;

use App\Ai\Agents\GroupIncidentAnalyzer;
use App\Ai\Agents\IncidentAnalyzer;
use App\Models\Incident;
use App\Models\IncidentGroup;
use App\Settings\NotificationSettings;
use Illuminate\Support\Facades\Log;

class AiService
{
    public function analyzeIncident(Incident $incident): array
    {
        Log::info('Analyze incident');
        $settings = app(NotificationSettings::class);
        $agent = new IncidentAnalyzer($incident);
        $response = $agent->ask(
            $agent->buildPrompt(), 
            provider: $settings->ai_provider
        );
        return json_decode($response, true) ?? [];
    }

    public function analyzeGroup(IncidentGroup $group): array
    {
        Log::info('Analyzing incident group', ['group_id' => $group->id]);
        $agent = new GroupIncidentAnalyzer($group);
        $response = $agent->prompt($agent->buildPrompt());
        return $response->toArray();
    }
}