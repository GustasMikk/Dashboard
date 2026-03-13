<?php

namespace App\Services;

use App\Ai\Agents\GroupIncidentAnalyzer;
use App\Models\IncidentGroup;
use App\Settings\NotificationSettings;
use Illuminate\Support\Facades\Log;

class AiService
{
    public function analyzeGroup(IncidentGroup $group): array
    {
        Log::info('Analyzing incident group', ['group_id' => $group->id]);
        $settings = app(NotificationSettings::class);
        $agent = new GroupIncidentAnalyzer($group);
        $model = $settings->ai_model !== 'auto' ? $settings->ai_model : null;

        $response = $model
            ? $agent->prompt($agent->buildPrompt(), provider: $settings->ai_provider, model: $model)
            : $agent->prompt($agent->buildPrompt(), provider: $settings->ai_provider);

        return $response->toArray();
    }
}
