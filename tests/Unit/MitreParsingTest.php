<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MitreParsingTest extends TestCase
{
    private function parseMitreBase(?string $mitreId): ?string
    {
        return $mitreId ? explode('.', $mitreId)[0] : null;
    }

    public function test_parses_sub_technique_to_base(): void
    {
        $this->assertEquals('T1110', $this->parseMitreBase('T1110.001'));
    }

    public function test_parses_another_sub_technique(): void
    {
        $this->assertEquals('T1566', $this->parseMitreBase('T1566.001'));
    }

    public function test_returns_base_technique_unchanged(): void
    {
        $this->assertEquals('T1110', $this->parseMitreBase('T1110'));
    }

    public function test_returns_null_when_no_mitre_id(): void
    {
        $this->assertNull($this->parseMitreBase(null));
    }

    public function test_parses_deeply_nested_sub_technique(): void
    {
        $this->assertEquals('T1110', $this->parseMitreBase('T1110.001.002'));
    }
}