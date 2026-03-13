<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->insert([
            ['group' => 'notifications', 'name' => 'email_enabled', 'payload' => json_encode(true), 'locked' => false],
            ['group' => 'notifications', 'name' => 'email_severities', 'payload' => json_encode(['critical', 'high']), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_generation_enabled', 'payload' => json_encode(true), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_severities', 'payload' => json_encode(['critical', 'high']), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_provider', 'payload' => json_encode('gemini'), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_model', 'payload' => json_encode('gemini-3-flash'), 'locked' => false],
            ['group' => 'notifications', 'name' => 'time_for_new_group', 'payload' => json_encode('15'), 'locked' => false],
            ['group' => 'notifications', 'name' => 'time_to_generate_ai_solution', 'payload' => json_encode('1'), 'locked' => false],
            ['group' => 'notifications', 'name' => 'ai_instructions', 'payload' => json_encode('You are an expert cybersecurity analyst working with a Wazuh SIEM system. Be specific to the actual data provided, never generic. Provide prioritized, actionable recommendations.'), 'locked' => false],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('group', 'notifications')->delete();
    }
};
