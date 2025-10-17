<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Git\Diff;

use PHPUnit\Framework\TestCase;

class FilterUtilTest extends TestCase
{
    public function testSanitize(): void
    {
        $sanitized = FilterUtil::sanitize(['A', 'C', 'Z']);

        $this->assertFalse(in_array('Z', $sanitized));
        $this->assertTrue(in_array('A', $sanitized));
        $this->assertTrue(in_array('C', $sanitized));
    }

    public function testSanitizeEmpty(): void
    {
        $sanitized = FilterUtil::sanitize(['Q', 'L', 'Z']);

        $this->assertEmpty($sanitized);
    }

    public function testFromConfigValue(): void
    {
        $this->assertEmpty(FilterUtil::filterFromConfigValue('FOO'));
        $this->assertEquals([], FilterUtil::filterFromConfigValue(null));
        $this->assertEquals([], FilterUtil::filterFromConfigValue(1));
        $this->assertEquals([], FilterUtil::filterFromConfigValue(true));
        $this->assertEquals([], FilterUtil::filterFromConfigValue(false));
        $this->assertEquals(['*'], FilterUtil::filterFromConfigValue('*'));
    }
}
