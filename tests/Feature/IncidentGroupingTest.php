<?php

namespace Tests\Feature;

use App\Jobs\ProcessWazuhAlert;
use App\Models\Comment;
use App\Models\Incident;
use App\Models\IncidentGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IncidentGroupingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('settings')->where('group', 'notifications')->delete();

        DB::table('settings')->insert([
            ['group' => 'notifications', 'name' => 'ai_generation_enabled', 'payload' => json_encode(false), 'locked' => false],
            ['group' => 'notifications', 'name' => 'email_enabled', 'payload' => json_encode(false), 'locked' => false],
            ['group' => 'notifications', 'name' => 'time_for_new_group', 'payload' => json_encode(15), 'locked' => false],
            ['group' => 'notifications', 'name' => 'time_to_generate_ai_solution', 'payload' => json_encode(0), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_severities', 'payload' => json_encode(['high', 'critical']), 'locked' => false],
            ['group' => 'notifications', 'name' => 'email_severities', 'payload' => json_encode(['high', 'critical']), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_provider', 'payload' => json_encode('gemini'), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_model', 'payload' => json_encode('auto'), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_instructions', 'payload' => json_encode('test'), 'locked' => false],
        ]);
    }

    private function sshPayload(string $ruleId = '5763', int $level = 10): array
    {
        return [
            'id'    => uniqid('wazuh_'),
            'rule'  => [
                'id'          => $ruleId,
                'description' => 'sshd: brute force trying to get access to the system.',
                'level'       => $level,
                'mitre'       => [
                    'id'        => ['T1110.001'],
                    'tactic'    => ['Credential Access'],
                    'technique' => ['Brute Force'],
                ],
            ],
            'agent' => [
                'id'   => '001',
                'name' => 'web-server-01',
            ],
        ];
    }

    public function test_creates_new_incident_from_wazuh_payload(): void
    {
        ProcessWazuhAlert::dispatchSync($this->sshPayload());

        $this->assertDatabaseCount('incidents', 1);
        $this->assertDatabaseHas('incidents', [
            'title' => 'sshd: brute force trying to get access to the system.',
        ]);
    }

    public function test_groups_incidents_with_same_rule_and_host_within_time_window(): void
    {
        ProcessWazuhAlert::dispatchSync($this->sshPayload());
        ProcessWazuhAlert::dispatchSync($this->sshPayload());

        $this->assertDatabaseCount('incidents', 1);
        $this->assertEquals(2, Incident::first()->occurrences_count);
    }

    public function test_creates_new_incident_after_time_window_expires(): void
    {
        ProcessWazuhAlert::dispatchSync($this->sshPayload());

        Incident::first()->update(['first_occurrence_at' => now()->subMinutes(20)]);

        ProcessWazuhAlert::dispatchSync($this->sshPayload());

        $this->assertDatabaseCount('incidents', 2);
    }

    public function test_creates_incident_group_when_mitre_id_is_present(): void
    {
        ProcessWazuhAlert::dispatchSync($this->sshPayload());

        $this->assertDatabaseCount('incident_groups', 1);
        $this->assertDatabaseHas('incident_groups', ['mitre_id' => 'T1110']);
    }

    public function test_updates_highest_severity_when_higher_severity_incident_arrives(): void
    {
        ProcessWazuhAlert::dispatchSync($this->sshPayload(ruleId: '5761', level: 5));
        ProcessWazuhAlert::dispatchSync($this->sshPayload(ruleId: '5762', level: 10));

        $this->assertEquals('high', IncidentGroup::first()->highest_severity);
    }
}