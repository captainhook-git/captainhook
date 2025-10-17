<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Config\Setup;

use Exception;
use PHPUnit\Framework\TestCase;

class GuidedTest extends TestCase
{
    public function testPHPActionOptionValidationValid(): void
    {
        $this->assertEquals('foo:bar', Guided::isPHPActionOptionValid('foo:bar'));
    }

    public function testPHPActionOptionValidationInvalid(): void
    {
        $this->expectException(Exception::class);

        Guided::isPHPActionOptionValid('foo-bar');
    }
}
