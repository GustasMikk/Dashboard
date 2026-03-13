<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WazuhService
{
    private string $baseUrl;

    private string $username;

    private string $password;

    public function __construct()
    {
        $this->baseUrl = config('services.wazuh.url');
        $this->username = config('services.wazuh.username');
        $this->password = config('services.wazuh.password');
    }

    public function getToken(): string
    {
        return Cache::remember('wazuh_token', 890, function () {
            $response = Http::withoutVerifying()
                ->withBasicAuth($this->username, $this->password)
                ->post("{$this->baseUrl}/security/user/authenticate");

            return $response->json('data.token');
        });
    }

    private function request(string $endpoint, array $params = []): array
    {
        $response = Http::withoutVerifying()
            ->withToken($this->getToken())
            ->get("{$this->baseUrl}{$endpoint}", $params);

        if ($response->status() === 401) {
            Cache::forget('wazuh_token');
            $response = Http::withoutVerifying()
                ->withToken($this->getToken())
                ->get("{$this->baseUrl}{$endpoint}", $params);
        }

        return $response->json() ?? [];
    }

    public function getAlerts(int $limit = 20): array
    {
        return $this->request('/alerts', ['limit' => $limit]);
    }

    public function getAgentSummary(): array
    {
        return $this->request('/agents/summary/status');
    }

    public function getAgents(): array
    {
        return $this->request('/agents');
    }

    public function getVulnerabilities(string $agentId = '000'): array
    {
        return $this->request("/vulnerability/{$agentId}");
    }
}
