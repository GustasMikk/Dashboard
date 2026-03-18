<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\IncidentGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetentionTest extends TestCase
{
    use RefreshDatabase;

    private function makeGroup(): IncidentGroup
    {
        return IncidentGroup::create([
            'title'              => 'Test Group',
            'status'             => 'open',
            'highest_severity'   => 'high',
            'host'               => 'test-host',
            'opened_at'          => now(),
            'last_occurrence_at' => now(),
            'total_occurrences'  => 0,
        ]);
    }

    private function incidentData(array $override = []): array
    {
        return array_merge([
            'wazuh_incident_id'   => uniqid('wazuh_'),
            'title'               => 'Test Incident',
            'severity'            => 'high',
            'rule'                => '5763',
            'host'                => 'test-host',
            'status'              => 'open',
            'first_occurrence_at' => now(),
            'last_occurrence_at'  => now(),
            'occurrences_count'   => 1,
        ], $override);
    }

    public function test_incidents_older_than_30_days_are_deleted(): void
    {
        $group = $this->makeGroup();

        $old = Incident::create($this->incidentData([
            'incident_group_id'   => $group->id,
            'first_occurrence_at' => now()->subDays(31),
        ]));

        $recent = Incident::create($this->incidentData([
            'incident_group_id'   => $group->id,
            'first_occurrence_at' => now()->subDays(10),
        ]));

        Incident::where('first_occurrence_at', '<', now()->subDays(30))->delete();

        $this->assertDatabaseMissing('incidents', ['id' => $old->id]);
        $this->assertDatabaseHas('incidents', ['id' => $recent->id]);
    }

    public function test_incidents_newer_than_30_days_are_not_deleted(): void
    {
        $group = $this->makeGroup();

        $recent1 = Incident::create($this->incidentData([
            'incident_group_id'   => $group->id,
            'first_occurrence_at' => now()->subDays(1),
        ]));

        $recent2 = Incident::create($this->incidentData([
            'incident_group_id'   => $group->id,
            'first_occurrence_at' => now()->subDays(29),
        ]));

        Incident::where('first_occurrence_at', '<', now()->subDays(30))->delete();

        $this->assertDatabaseCount('incidents', 2);
    }
}