<?php

namespace Tests\Unit;

use App\Support\SpreadsheetValueSanitizer;
use PHPUnit\Framework\TestCase;

class SpreadsheetValueSanitizerTest extends TestCase
{
    public function test_formula_like_values_are_prefixed_with_an_apostrophe(): void
    {
        foreach (['=SUM(1,1)', '+CMD', '-1+1', '@HYPERLINK', "\t=1", "\r=1", "\n=1"] as $value) {
            $this->assertSame("'".$value, SpreadsheetValueSanitizer::sanitize($value));
        }
    }

    public function test_normal_values_are_preserved(): void
    {
        $this->assertSame('Budi Rider', SpreadsheetValueSanitizer::sanitize('Budi Rider'));
        $this->assertSame('D 1234 ABC', SpreadsheetValueSanitizer::sanitize('D 1234 ABC'));
        $this->assertSame('unrider-01', SpreadsheetValueSanitizer::sanitize('unrider-01'));
    }
}
