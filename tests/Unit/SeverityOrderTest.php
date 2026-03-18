<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SeverityOrderTest extends TestCase
{
    private array $severityOrder = [
        'low'      => 1,
        'medium'   => 2,
        'high'     => 3,
        'critical' => 4,
    ];

    private function isHigher(string $new, string $current): bool
    {
        return ($this->severityOrder[$new] ?? 0) > ($this->severityOrder[$current] ?? 0);
    }

    public function test_critical_is_higher_than_high(): void
    {
        $this->assertTrue($this->isHigher('critical', 'high'));
    }

    public function test_high_is_higher_than_medium(): void
    {
        $this->assertTrue($this->isHigher('high', 'medium'));
    }

    public function test_medium_is_higher_than_low(): void
    {
        $this->assertTrue($this->isHigher('medium', 'low'));
    }

    public function test_same_severity_is_not_higher(): void
    {
        $this->assertFalse($this->isHigher('high', 'high'));
    }

    public function test_lower_severity_is_not_higher(): void
    {
        $this->assertFalse($this->isHigher('low', 'critical'));
    }

    public function test_unknown_severity_is_not_higher(): void
    {
        $this->assertFalse($this->isHigher('unknown', 'low'));
    }

    public function test_critical_is_highest(): void
    {
        foreach (['low', 'medium', 'high'] as $severity) {
            $this->assertTrue($this->isHigher('critical', $severity));
        }
    }

    public function test_low_is_lowest(): void
    {
        foreach (['medium', 'high', 'critical'] as $severity) {
            $this->assertFalse($this->isHigher('low', $severity));
        }
    }
}