<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected string $apiKey;
    protected string $model;
    protected string $provider;

    public function __construct()
    {
        $this->apiKey   = config('services.ai.key');
        $this->model    = config('services.ai.model');
        $this->provider = config('services.ai.provider');
    }

    public function analyzeIncident(array $incident): array
    {
        return match($this->provider) {
            'gemini'    => $this->callGemini($incident),
            'openai'    => $this->callOpenAI($incident),
            'anthropic' => $this->callAnthropic($incident),
            default     => throw new \Exception("Unsupported AI provider: {$this->provider}"),
        };

        return $result;
    }

    protected function callGemini(array $incident): array
    {
        Log::info('Sending to Gemini', ['incident_id' => $incident['id']]);
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";

        $response = Http::post("{$endpoint}?key={$this->apiKey}", [
            'contents' => [
                ['parts' => [['text' => $this->buildPrompt($incident)]]]
            ],
            'generationConfig' => ['responseMimeType' => 'application/json'],
        ]);

        $text = $response->json('candidates.0.content.parts.0.text');
        return json_decode($text, true) ?? [];
    }

    protected function callOpenAI(array $incident): array
    {
        $response = Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'    => $this->model, // e.g. gpt-4o-mini
                'messages' => [
                    ['role' => 'user', 'content' => $this->buildPrompt($incident)]
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

        $text = $response->json('choices.0.message.content');
        return json_decode($text, true) ?? [];
    }

    protected function callAnthropic(array $incident): array
    {
        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model, // e.g. claude-haiku-4-5
            'max_tokens' => 1024,
            'messages'   => [
                ['role' => 'user', 'content' => $this->buildPrompt($incident)]
            ],
        ]);

        $text = $response->json('content.0.text');
        return json_decode($text, true) ?? [];
    }

    protected function buildPrompt(array $incident): string
    {
        return <<<PROMPT
        You are a cybersecurity analyst. Analyze this security incident and respond ONLY with a JSON object, no markdown.

        Incident data:
        - Title: {$incident['title']}
        - Severity: {$incident['severity']}
        - Rule: {$incident['rule']}
        - Host: {$incident['host']}
        

        Respond with exactly this JSON structure:
        {
            "ai_description": "2-3 sentence plain english description of what happened",
            "ai_root_cause": "likely root cause explanation",
            "ai_recommendations": "actionable steps to resolve and prevent recurrence"
        }
        PROMPT;
    }
}