<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

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
