<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App;

use Exception;
use PHPUnit\Framework\TestCase;

class HooksTest extends TestCase
{
    public function testHookArguments(): void
    {
        $this->assertEquals('', Hooks::getOriginalHookArguments('pre-commit'));
        $this->assertEquals(' {$PREVIOUSHEAD} {$NEWHEAD} {$MODE}', Hooks::getOriginalHookArguments('post-checkout'));
    }

    public function testGetVirtualHook(): void
    {
        $this->assertEquals('post-change', Hooks::getVirtualHook('post-rewrite'));
    }

    public function testGetNativeHooksForVirtualHookWithVirtual(): void
    {
        $hooks = Hooks::getNativeHooksForVirtualHook(Hooks::POST_CHANGE);

        $this->assertTrue(in_array(Hooks::POST_CHECKOUT, $hooks));
        $this->assertTrue(in_array(Hooks::POST_MERGE, $hooks));
        $this->assertTrue(in_array(Hooks::POST_REWRITE, $hooks));
    }

    public function testGetNativeHooksForVirtualHookWithNative(): void
    {
        $hooks = Hooks::getNativeHooksForVirtualHook(Hooks::PRE_COMMIT);

        $this->assertEmpty($hooks);
    }

    public function testGetVirtualHookFail(): void
    {
        $this->expectException(Exception::class);
        Hooks::getVirtualHook('pre-commit');
    }

    public function testReceivesStdIn(): void
    {
        $this->assertTrue(Hooks::receivesStdIn(Hooks::PRE_PUSH));
        $this->assertTrue(Hooks::receivesStdIn(Hooks::POST_REWRITE));
        $this->assertFalse(Hooks::receivesStdIn(Hooks::PRE_COMMIT));
    }
}
