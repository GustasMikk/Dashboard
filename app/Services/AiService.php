<?php

namespace App\Services;

use App\Ai\Agents\GroupIncidentAnalyzer;
use App\Models\IncidentGroup;
use App\Settings\AppSettings;

class AiService
{
    public function analyzeGroup(IncidentGroup $group): array
    {
        $settings = app(AppSettings::class);
        $agent = new GroupIncidentAnalyzer($group);
        $model = $settings->ai_model !== 'auto' ? $settings->ai_model : null;

        $response = $model
            ? $agent->prompt($agent->buildPrompt(), provider: $settings->ai_provider, model: $model)
            : $agent->prompt($agent->buildPrompt(), provider: $settings->ai_provider);

        return $response->toArray();
    }
}
