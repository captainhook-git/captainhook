<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Config;

use PHPUnit\Framework\TestCase;

class ConditionTest extends TestCase
{
    public function testGetExec(): void
    {
        $config = new Condition('\\Foo\\Bar');

        $this->assertEquals('\\Foo\\Bar', $config->getExec());
    }

    public function testGetEmptyArgs(): void
    {
        $config = new Condition('\\Foo\\Bar');

        $this->assertEquals([], $config->getArgs());
    }
}
