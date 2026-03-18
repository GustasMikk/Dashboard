<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\IncidentGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin1;
    protected IncidentGroup $group1;
    protected IncidentGroup $group2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin1 = User::create([
            'name'     => 'Admin1',
            'email'    => 'admin1@admin.com',
            'password' => Hash::make('admin1'),
        ]);

        $this->group1 = IncidentGroup::create([
            'title'              => 'Brute Force Campaign',
            'status'             => 'open',
            'highest_severity'   => 'critical',
            'host'               => 'web-server-01',
            'mitre_id'           => 'T1110',
            'opened_at'          => now(),
            'last_occurrence_at' => now(),
            'total_occurrences'  => 0,
        ]);

        $this->group2 = IncidentGroup::create([
            'title'              => 'Phishing Campaign',
            'status'             => 'closed',
            'highest_severity'   => 'medium',
            'host'               => 'mail-server-01',
            'mitre_id'           => 'T1566',
            'opened_at'          => now(),
            'last_occurrence_at' => now(),
            'total_occurrences'  => 0,
        ]);

        Incident::create($this->incidentData(['severity' => 'critical', 'status' => 'open',     'host' => 'web-server-01',  'rule' => '5001', 'incident_group_id' => $this->group1->id]));
        Incident::create($this->incidentData(['severity' => 'high',     'status' => 'open',     'host' => 'web-server-01',  'rule' => '5002', 'incident_group_id' => $this->group1->id]));
        Incident::create($this->incidentData(['severity' => 'high',     'status' => 'resolved', 'host' => 'db-server-01',   'rule' => '5003', 'incident_group_id' => $this->group1->id]));
        Incident::create($this->incidentData(['severity' => 'medium',   'status' => 'open',     'host' => 'db-server-01',   'rule' => '5004', 'incident_group_id' => $this->group2->id]));
        Incident::create($this->incidentData(['severity' => 'medium',   'status' => 'closed',   'host' => 'mail-server-01', 'rule' => '5005', 'incident_group_id' => $this->group2->id]));
        Incident::create($this->incidentData(['severity' => 'low',      'status' => 'closed',   'host' => 'mail-server-01', 'rule' => '5006', 'incident_group_id' => null]));
        Incident::create($this->incidentData(['severity' => 'low',      'status' => 'open',     'host' => 'web-server-01',  'rule' => '5007', 'incident_group_id' => null]));
        Incident::create($this->incidentData(['severity' => 'critical', 'status' => 'resolved', 'host' => 'db-server-01',   'rule' => '5008', 'incident_group_id' => $this->group1->id]));
    }

    // Severity Filters

    public function test_filter_by_critical_severity(): void
    {
        $results = Incident::where('severity', 'critical')->get();
        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($i) => $i->severity === 'critical'));
    }

    public function test_filter_by_high_severity(): void
    {
        $results = Incident::where('severity', 'high')->get();
        $this->assertCount(2, $results);
        $this->assertTrue($results->every(fn($i) => $i->severity === 'high'));
    }

    public function test_filter_by_medium_severity(): void
    {
        $results = Incident::where('severity', 'medium')->get();
        $this->assertCount(2, $results);
    }

    public function test_filter_by_low_severity(): void
    {
        $results = Incident::where('severity', 'low')->get();
        $this->assertCount(2, $results);
    }

    // Host Filter

    public function test_filter_by_host(): void
    {
        $results = Incident::where('host', 'web-server-01')->get();
        $this->assertCount(3, $results);
        $this->assertTrue($results->every(fn($i) => $i->host === 'web-server-01'));
    }

    // Combined Filter

    public function test_filter_by_host_and_severity(): void
    {
        $results = Incident::where('host', 'db-server-01')
            ->where('severity', 'high')
            ->get();
        $this->assertCount(1, $results);
    }

    // Incident Group Filters

    public function test_filter_grouped_incidents(): void
    {
        $results = Incident::whereNotNull('incident_group_id')->get();
        $this->assertCount(6, $results);
    }

    public function test_filter_ungrouped_incidents(): void
    {
        $results = Incident::whereNull('incident_group_id')->get();
        $this->assertCount(2, $results);
    }

    public function test_filter_by_incident_group(): void
    {
        $results = Incident::where('incident_group_id', $this->group1->id)->get();
        $this->assertCount(4, $results);
    }

    // Incident Group Filters

    public function test_filter_groups_by_status_open(): void
    {
        $results = IncidentGroup::where('status', 'open')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Brute Force Campaign', $results->first()->title);
    }

    public function test_filter_groups_by_highest_severity(): void
    {
        $results = IncidentGroup::where('highest_severity', 'critical')->get();
        $this->assertCount(1, $results);
    }

    public function test_filter_groups_by_mitre_id(): void
    {
        $results = IncidentGroup::where('mitre_id', 'T1110')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('web-server-01', $results->first()->host);
    }

    // Sorting

    public function test_sort_incidents_by_last_occurrence_desc(): void
    {
        $results = Incident::orderBy('last_occurrence_at', 'desc')->get();
        $this->assertEquals(
            $results->sortByDesc('last_occurrence_at')->pluck('id')->toArray(),
            $results->pluck('id')->toArray()
        );
    }

    private function incidentData(array $override = []): array
    {
        return array_merge([
            'wazuh_incident_id'  => uniqid('wazuh_'),
            'title'              => 'Test Incident',
            'severity'           => 'medium',
            'rule'               => '5000',
            'host'               => 'test-host',
            'status'             => 'open',
            'first_occurrence_at'=> now(),
            'last_occurrence_at' => now(),
            'occurrences_count'  => 1,
        ], $override);
    }
}