<?php

namespace Database\Seeders;

use App\Models\IncidentGroup;
use Illuminate\Database\Seeder;

class IncidentGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'title'              => 'Credential Access on web-server-01',
                'mitre_id'           => 'T1110',
                'highest_severity'   => 'critical',
                'host'               => 'web-server-01',
                'status'             => 'open',
                'opened_at'          => now()->subDays(2),
                'last_occurrence_at' => now()->subHours(1),
                'total_occurrences'  => 45,
            ],
            [
                'title'              => 'Phishing Campaign on mail-server-01',
                'mitre_id'           => 'T1566',
                'highest_severity'   => 'high',
                'host'               => 'mail-server-01',
                'status'             => 'open',
                'opened_at'          => now()->subDays(1),
                'last_occurrence_at' => now()->subHours(3),
                'total_occurrences'  => 12,
            ],
            [
                'title'              => 'Lateral Movement on db-server-01',
                'mitre_id'           => 'T1021',
                'highest_severity'   => 'high',
                'host'               => 'db-server-01',
                'status'             => 'resolved',
                'opened_at'          => now()->subDays(5),
                'last_occurrence_at' => now()->subDays(3),
                'total_occurrences'  => 8,
                'resolved_at'        => now()->subDays(3),
            ],
            [
                'title'              => 'Defense Evasion on web-server-01',
                'mitre_id'           => 'T1562',
                'highest_severity'   => 'medium',
                'host'               => 'web-server-01',
                'status'             => 'closed',
                'opened_at'          => now()->subDays(10),
                'last_occurrence_at' => now()->subDays(8),
                'total_occurrences'  => 3,
                'resolved_at'        => now()->subDays(9),
                'closed_at'          => now()->subDays(8),
            ],
            [
                'title'              => 'Discovery Activity on db-server-01',
                'mitre_id'           => 'T1083',
                'highest_severity'   => 'low',
                'host'               => 'db-server-01',
                'status'             => 'open',
                'opened_at'          => now()->subHours(5),
                'last_occurrence_at' => now()->subHours(1),
                'total_occurrences'  => 2,
            ],
        ];

        foreach ($groups as $group) {
            IncidentGroup::create($group);
        }
    }
}