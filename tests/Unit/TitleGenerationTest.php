<?php

namespace Tests\Unit;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class TitleGenerationTest extends TestCase
{
    private function generateTitle(string $tactic, string $host, Carbon $datetime): string
    {
        return "{$tactic} on {$host} at " . $datetime->format('Y-m-d H:i');
    }

    public function test_title_contains_tactic(): void
    {
        $title = $this->generateTitle('Credential Access', 'web-server-01', Carbon::now());
        $this->assertStringContainsString('Credential Access', $title);
    }

    public function test_title_contains_host(): void
    {
        $title = $this->generateTitle('Credential Access', 'web-server-01', Carbon::now());
        $this->assertStringContainsString('web-server-01', $title);
    }

    public function test_title_contains_formatted_datetime(): void
    {
        $datetime = Carbon::create(2026, 3, 10, 9, 30, 0);
        $title = $this->generateTitle('Credential Access', 'web-server-01', $datetime);
        $this->assertStringContainsString('2026-03-10 09:30', $title);
    }

    public function test_title_format_is_correct(): void
    {
        $datetime = Carbon::create(2026, 3, 10, 9, 30, 0);
        $title = $this->generateTitle('Brute Force', 'db-server-01', $datetime);
        $this->assertEquals('Brute Force on db-server-01 at 2026-03-10 09:30', $title);
    }

    public function test_title_with_different_tactic(): void
    {
        $title = $this->generateTitle('Lateral Movement', 'mail-server-01', Carbon::now());
        $this->assertStringContainsString('Lateral Movement', $title);
        $this->assertStringContainsString('mail-server-01', $title);
    }
}