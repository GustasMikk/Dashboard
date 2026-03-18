<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SeverityMappingTest extends TestCase
{
    private function mapSeverity(int $level): string
    {
        return match(true) {
            $level >= 12 => 'critical',
            $level >= 8  => 'high',
            $level >= 4  => 'medium',
            default      => 'low',
        };
    }

    public function test_level_15_maps_to_critical(): void
    {
        $this->assertEquals('critical', $this->mapSeverity(15));
    }

    public function test_level_12_maps_to_critical(): void
    {
        $this->assertEquals('critical', $this->mapSeverity(12));
    }

    public function test_level_11_maps_to_high(): void
    {
        $this->assertEquals('high', $this->mapSeverity(11));
    }

    public function test_level_8_maps_to_high(): void
    {
        $this->assertEquals('high', $this->mapSeverity(8));
    }

    public function test_level_7_maps_to_medium(): void
    {
        $this->assertEquals('medium', $this->mapSeverity(7));
    }

    public function test_level_4_maps_to_medium(): void
    {
        $this->assertEquals('medium', $this->mapSeverity(4));
    }

    public function test_level_3_maps_to_low(): void
    {
        $this->assertEquals('low', $this->mapSeverity(3));
    }

    public function test_level_0_maps_to_low(): void
    {
        $this->assertEquals('low', $this->mapSeverity(0));
    }
}