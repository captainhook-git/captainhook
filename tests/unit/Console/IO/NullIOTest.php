<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Console\IO;

use PHPUnit\Framework\TestCase;

class NullIOTest extends TestCase
{
    public function testGetArguments(): void
    {
        $io = new NullIO();
        $this->assertEquals([], $io->getArguments());
    }

    public function testGetArgument(): void
    {
        $io = new NullIO();
        $this->assertEquals('', $io->getArgument('foo'));
        $this->assertEquals('bar', $io->getArgument('foo', 'bar'));
    }

    public function testGetStandardInput(): void
    {
        $io = new NullIO();
        $this->assertEquals([], $io->getStandardInput());
    }

    public function testIsInteractive(): void
    {
        $io = new NullIO();
        $this->assertFalse($io->isInteractive());
    }

    public function testIsDebug(): void
    {
        $io = new NullIO();
        $this->assertFalse($io->isDebug());
    }

    public function testIsVerbose(): void
    {
        $io = new NullIO();
        $this->assertFalse($io->isVerbose());
    }

    public function testIsVeryVerbose(): void
    {
        $io = new NullIO();
        $this->assertFalse($io->isVeryVerbose());
    }

    public function testWriteError(): void
    {
        $io = new NullIO();
        $io->writeError('foo');
        $this->assertTrue(true);
    }

    public function testAsk(): void
    {
        $io = new NullIO();
        $this->assertEquals('bar', $io->ask('foo', 'bar'));
    }

    public function testAskConfirmation(): void
    {
        $io = new NullIO();
        $this->assertEquals(true, $io->askConfirmation('foo', true));
    }

    public function testAskAndValidate(): void
    {
        $io = new NullIO();
        $this->assertEquals(
            true,
            $io->askAndValidate(
                'foo',
                function () {
                    return true;
                },
                false,
                true
            )
        );
    }
}
