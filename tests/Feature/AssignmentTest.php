<?php

namespace Tests\Feature;

use App\Models\IncidentGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected IncidentGroup $group1;
    protected IncidentGroup $group2;
    protected User $admin1;
    protected User $admin2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin1 = User::create([
            'name'     => 'Admin1',
            'email'    => 'admin1@admin.com',
            'password' => Hash::make('admin1'),
        ]);

        $this->admin2 = User::create([
            'name'     => 'Admin2',
            'email'    => 'admin2@admin.com',
            'password' => Hash::make('admin2'),
        ]);

        $this->group1 = IncidentGroup::create([
            'title'              => 'Test Group',
            'status'             => 'open',
            'highest_severity'   => 'high',
            'host'               => 'test-host',
            'opened_at'          => now(),
            'last_occurrence_at' => now(),
            'total_occurrences'  => 0,
        ]);

        $this->group2 = IncidentGroup::create([
            'title'              => 'Test Group 2',
            'status'             => 'open',
            'highest_severity'   => 'high',
            'host'               => 'test-host-2',
            'opened_at'          => now(),
            'last_occurrence_at' => now(),
            'total_occurrences'  => 0,
        ]);
    }

    public function test_assign_users_to_incident_groups(): void
    {
        $this->group1->update(['assigned_user_id' => $this->admin1->id]);
        $this->group2->update(['assigned_user_id' => $this->admin2->id]);

        $this->assertEquals($this->admin1->id, $this->group1->fresh()->assigned_user_id);
        $this->assertEquals($this->admin2->id, $this->group2->fresh()->assigned_user_id);
    }
}
