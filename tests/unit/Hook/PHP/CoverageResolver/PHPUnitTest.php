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

class PHPUnitTest extends TestCase
{
    public function testValid(): void
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('not tested on windows');
        }

        $resolver = new PHPUnit(CH_PATH_FILES . '/bin/phpunit');
        $coverage = $resolver->getCoverage();

        $this->assertEquals(95, $coverage);
    }

    public function testPHPUnitError(): void
    {
        $this->expectException(Exception::class);

        $resolver = new PHPUnit(CH_PATH_FILES . '/bin/failure');
        $resolver->getCoverage();
    }
}
