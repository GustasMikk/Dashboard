<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeIncidentGroupJob;
use App\Jobs\ProcessWazuhAlert;
use App\Models\IncidentGroup;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AiGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('settings')->where('group', 'notifications')->delete();
        DB::table('settings')->insert([
            ['group' => 'notifications', 'name' => 'ai_generation_enabled', 'payload' => json_encode(true), 'locked' => false],
            ['group' => 'notifications', 'name' => 'email_enabled','payload' => json_encode(false), 'locked' => false],
            ['group' => 'notifications', 'name' => 'time_for_new_group','payload' => json_encode(15), 'locked' => false],
            ['group' => 'notifications', 'name' => 'time_to_generate_ai_solution', 'payload' => json_encode(2), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_severities', 'payload' => json_encode(['high', 'critical']), 'locked' => false],
            ['group' => 'notifications', 'name' => 'email_severities', 'payload' => json_encode(['high', 'critical']), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_provider', 'payload' => json_encode('gemini'), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_model', 'payload' => json_encode('auto'), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_instructions', 'payload' => json_encode('test'), 'locked' => false],
        ]);
    }

    public function test_ai_analyze_group_job_is_dispatched_for_high_severity(): void
    {
        Queue::fake();

        (new ProcessWazuhAlert($this->highSeverityPayload()))->handle();

        Queue::assertPushed(AnalyzeIncidentGroupJob::class);
    }

    public function test_ai_analyze_group_job_is_not_dispatched_for_low_severity(): void
    {
        Queue::fake();

        (new ProcessWazuhAlert($this->lowSeverityPayload()))->handle();

        Queue::assertNotPushed(AnalyzeIncidentGroupJob::class);
    }

    public function test_ai_updates_incident_group_with_analysis(): void
    {
        $group = IncidentGroup::create([
            'title'              => 'Test Group',
            'status'             => 'open',
            'highest_severity'   => 'high',
            'host'               => 'web-server-01',
            'mitre_id'           => 'T1110',
            'opened_at'          => now(),
            'last_occurrence_at' => now(),
            'total_occurrences'  => 1,
        ]);

        $this->mock(AiService::class, function ($mock) {
            $mock->shouldReceive('analyzeGroup')
                ->once()
                ->andReturn([
                    'ai_description'     => 'Mocked description',
                    'ai_root_cause'      => 'Mocked root cause',
                    'ai_recommendations' => 'Mocked recommendations',
                ]);
        });

        (new AnalyzeIncidentGroupJob($group))->handle();

        $group->refresh();

        $this->assertEquals('Mocked description', $group->ai_description);
        $this->assertEquals('Mocked root cause', $group->ai_root_cause);
        $this->assertEquals('Mocked recommendations', $group->ai_recommendations);
    }

    public function test_ai_not_generated_when_disabled(): void
    {
        DB::table('settings')
            ->where('group', 'notifications')
            ->where('name', 'ai_generation_enabled')
            ->update(['payload' => json_encode(false)]);

        Queue::fake();

        ProcessWazuhAlert::dispatchSync($this->highSeverityPayload());

        Queue::assertNotPushed(AnalyzeIncidentGroupJob::class);
    }

    public function test_ai_job_skips_if_newer_job_scheduled(): void
    {
        $group = IncidentGroup::create([
            'title'              => 'Test Group',
            'status'             => 'open',
            'highest_severity'   => 'high',
            'host'               => 'web-server-01',
            'mitre_id'           => 'T1110',
            'opened_at'          => now(),
            'last_occurrence_at' => now(),
            'total_occurrences'  => 1,
            'ai_scheduled_at'    => now()->addMinutes(5),
        ]);

        $aiService = $this->mock(AiService::class);
        $aiService->shouldNotReceive('analyzeGroup');

        (new AnalyzeIncidentGroupJob($group))->handle();
    }

    private function highSeverityPayload(): array
    {
        return [
            'id'    => uniqid('wazuh_'),
            'rule'  => [
                'id'          => '5763',
                'description' => 'sshd: brute force trying to get access to the system.',
                'level'       => 15,
                'mitre'       => [
                    'id'        => ['T1110.001'],
                    'tactic'    => ['Credential Access'],
                    'technique' => ['Brute Force'],
                ],
            ],
            'agent' => ['id' => '001', 'name' => 'web-server-01'],
        ];
    }

    private function lowSeverityPayload(): array
    {
        return [
            'id'    => uniqid('wazuh_'),
            'rule'  => [
                'id'          => '5001',
                'description' => 'Low severity alert.',
                'level'       => 2,
                'mitre'       => [
                    'id'        => ['T1110.001'],
                    'tactic'    => ['Credential Access'],
                    'technique' => ['Brute Force'],
                ],
            ],
            'agent' => ['id' => '001', 'name' => 'web-server-01'],
        ];
    }
}