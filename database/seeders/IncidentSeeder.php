<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\IncidentGroup;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        $groups = IncidentGroup::all()->keyBy('mitre_id');

        $incidents = [
            [
                'wazuh_incident_id'   => 'wazuh_001',
                'incident_group_id'   => $groups['T1110']->id ?? null,
                'mitre_id'            => 'T1110',
                'title'               => 'sshd: brute force trying to get access to the system.',
                'severity'            => 'critical',
                'rule'                => '5763',
                'host'                => 'web-server-01',
                'first_occurrence_at' => now()->subDays(2),
                'last_occurrence_at'  => now()->subHours(1),
                'occurrences_count'   => 20,
                'raw_payload'         => json_encode(['rule' => ['id' => '5763', 'level' => 12]]),
            ],
            [
                'wazuh_incident_id'   => 'wazuh_002',
                'incident_group_id'   => $groups['T1110']->id ?? null,
                'mitre_id'            => 'T1110',
                'title'               => 'PAM: User login failed.',
                'severity'            => 'high',
                'rule'                => '5503',
                'host'                => 'web-server-01',
                'first_occurrence_at' => now()->subDays(2),
                'last_occurrence_at'  => now()->subHours(2),
                'occurrences_count'   => 15,
                'raw_payload'         => json_encode(['rule' => ['id' => '5503', 'level' => 10]]),
            ],
            [
                'wazuh_incident_id'   => 'wazuh_003',
                'incident_group_id'   => $groups['T1110']->id ?? null,
                'mitre_id'            => 'T1110',
                'title'               => 'syslog: User missed the password more than one time.',
                'severity'            => 'high',
                'rule'                => '2502',
                'host'                => 'web-server-01',
                'first_occurrence_at' => now()->subDays(1),
                'last_occurrence_at'  => now()->subHours(3),
                'occurrences_count'   => 10,
                'raw_payload'         => json_encode(['rule' => ['id' => '2502', 'level' => 10]]),
            ],
            [
                'wazuh_incident_id'   => 'wazuh_004',
                'incident_group_id'   => $groups['T1566']->id ?? null,
                'mitre_id'            => 'T1566',
                'title'               => 'Suspicious email attachment detected.',
                'severity'            => 'high',
                'rule'                => '8001',
                'host'                => 'mail-server-01',
                'first_occurrence_at' => now()->subDays(1),
                'last_occurrence_at'  => now()->subHours(3),
                'occurrences_count'   => 5,
                'raw_payload'         => json_encode(['rule' => ['id' => '8001', 'level' => 10]]),
            ],
            [
                'wazuh_incident_id'   => 'wazuh_005',
                'incident_group_id'   => $groups['T1566']->id ?? null,
                'mitre_id'            => 'T1566',
                'title'               => 'Phishing URL detected in email.',
                'severity'            => 'medium',
                'rule'                => '8002',
                'host'                => 'mail-server-01',
                'first_occurrence_at' => now()->subDays(1),
                'last_occurrence_at'  => now()->subHours(4),
                'occurrences_count'   => 7,
                'raw_payload'         => json_encode(['rule' => ['id' => '8002', 'level' => 7]]),
            ],
            [
                'wazuh_incident_id'   => 'wazuh_006',
                'incident_group_id'   => null,
                'mitre_id'            => null,
                'title'               => 'System auditing configuration changed.',
                'severity'            => 'low',
                'rule'                => '9002',
                'host'                => 'db-server-01',
                'first_occurrence_at' => now()->subHours(2),
                'last_occurrence_at'  => now()->subHours(2),
                'occurrences_count'   => 1,
                'raw_payload'         => json_encode(['rule' => ['id' => '9002', 'level' => 3]]),
            ],
            [
                'wazuh_incident_id'   => 'wazuh_007',
                'incident_group_id'   => null,
                'mitre_id'            => null,
                'title'               => 'New user added to the system.',
                'severity'            => 'medium',
                'rule'                => '5901',
                'host'                => 'web-server-01',
                'first_occurrence_at' => now()->subDays(3),
                'last_occurrence_at'  => now()->subDays(3),
                'occurrences_count'   => 1,
                'raw_payload'         => json_encode(['rule' => ['id' => '5901', 'level' => 6]]),
            ],
        ];

        foreach ($incidents as $incident) {
            Incident::create($incident);
        }
    }
}