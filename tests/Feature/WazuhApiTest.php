<?php

namespace Tests\Feature;

use App\Services\WazuhService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WazuhApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_get_token_returns_token(): void
    {
        Http::fake([
            '*/security/user/authenticate' => Http::response([
                'data' => ['token' => 'fake-jwt-token']
            ], 200),
        ]);

        $service = new WazuhService();
        $token = $service->getToken();

        $this->assertEquals('fake-jwt-token', $token);
    }

    public function test_get_token_is_cached(): void
    {
        Http::fake([
            '*/security/user/authenticate' => Http::response([
                'data' => ['token' => 'fake-jwt-token']
            ], 200),
        ]);

        $service = new WazuhService();
        $service->getToken();
        $service->getToken(); // second call should use cache

        Http::assertSentCount(1); // only one auth request
    }

    public function test_get_alerts_returns_data(): void
    {
        Http::fake([
            '*/security/user/authenticate' => Http::response([
                'data' => ['token' => 'fake-jwt-token']
            ], 200),
            '*/alerts*' => Http::response([
                'data' => [
                    'affected_items' => [
                        ['id' => '1', 'rule' => ['description' => 'Test alert', 'level' => 10]],
                        ['id' => '2', 'rule' => ['description' => 'Another alert', 'level' => 5]],
                    ],
                    'total_affected_items' => 2,
                ]
            ], 200),
        ]);

        $service = new WazuhService();
        $result = $service->getAlerts();

        $this->assertArrayHasKey('data', $result);
        $this->assertCount(2, $result['data']['affected_items']);
    }

    public function test_get_agents_returns_data(): void
    {
        Http::fake([
            '*/security/user/authenticate' => Http::response([
                'data' => ['token' => 'fake-jwt-token']
            ], 200),
            '*/agents*' => Http::response([
                'data' => [
                    'affected_items' => [
                        ['id' => '001', 'name' => 'web-server-01', 'status' => 'active'],
                        ['id' => '002', 'name' => 'db-server-01',  'status' => 'active'],
                    ],
                    'total_affected_items' => 2,
                ]
            ], 200),
        ]);

        $service = new WazuhService();
        $result = $service->getAgents();

        $this->assertArrayHasKey('data', $result);
        $this->assertCount(2, $result['data']['affected_items']);
    }

    public function test_get_agent_summary_returns_data(): void
    {
        Http::fake([
            '*/security/user/authenticate' => Http::response([
                'data' => ['token' => 'fake-jwt-token']
            ], 200),
            '*/agents/summary/status*' => Http::response([
                'data' => [
                    'connection' => ['active' => 5, 'disconnected' => 1],
                ]
            ], 200),
        ]);

        $service = new WazuhService();
        $result = $service->getAgentSummary();

        $this->assertArrayHasKey('data', $result);
    }

    public function test_retries_with_new_token_on_401(): void
    {
        Http::fake([
            '*/security/user/authenticate' => Http::sequence()
                ->push(['data' => ['token' => 'expired-token']], 200)
                ->push(['data' => ['token' => 'new-token']], 200),
            '*/alerts*' => Http::sequence()
                ->push([], 401)      // first attempt fails
                ->push([             // second attempt succeeds
                    'data' => ['affected_items' => [], 'total_affected_items' => 0]
                ], 200),
        ]);

        $service = new WazuhService();
        $result = $service->getAlerts();

        $this->assertArrayHasKey('data', $result);
    }

    public function test_get_vulnerabilities_for_agent(): void
    {
        Http::fake([
            '*/security/user/authenticate' => Http::response([
                'data' => ['token' => 'fake-jwt-token']
            ], 200),
            '*/vulnerability/001*' => Http::response([
                'data' => [
                    'affected_items' => [
                        ['cve' => 'CVE-2021-44228', 'severity' => 'critical'],
                    ],
                    'total_affected_items' => 1,
                ]
            ], 200),
        ]);

        $service = new WazuhService();
        $result = $service->getVulnerabilities('001');

        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('CVE-2021-44228', $result['data']['affected_items'][0]['cve']);
    }
}