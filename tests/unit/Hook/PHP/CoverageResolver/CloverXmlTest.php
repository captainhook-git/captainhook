<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\PHP\CoverageResolver;

use Exception;
use PHPUnit\Framework\TestCase;

class CloverXmlTest extends TestCase
{
    public function testValid(): void
    {
        $resolver = new CloverXML(CH_PATH_FILES . '/coverage/valid.xml');
        $coverage = $resolver->getCoverage();

        $this->assertEquals(95, $coverage);
    }

    public function testValid100Percent(): void
    {
        $resolver = new CloverXML(CH_PATH_FILES . '/coverage/valid-100.xml');
        $coverage = $resolver->getCoverage();

        $this->assertEquals(100, $coverage);
    }

    public function testFileNotFound(): void
    {
        $this->expectException(Exception::class);

        $resolver = new CloverXML('foo.xml');
        $this->assertNull($resolver);
    }

    public function testCrapMetrics(): void
    {
        $this->expectException(Exception::class);

        $resolver = new CloverXML(CH_PATH_FILES . '/coverage/invalid-crap.xml');
        $resolver->getCoverage();
    }

    public function testInvalidXML(): void
    {
        $this->expectException(Exception::class);

        $resolver = new CloverXML(CH_PATH_FILES . '/coverage/no-metrics.xml');
        $this->assertNull($resolver);
    }

    public function testInvalidMetrics(): void
    {
        $this->expectException(Exception::class);

        $resolver = new CloverXML(CH_PATH_FILES . '/coverage/invalid-metrics.xml');
        $resolver->getCoverage();
    }
}
