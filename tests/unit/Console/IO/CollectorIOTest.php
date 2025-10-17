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

class CollectorIOTest extends TestCase
{
    use Mockery;

    public function testGetArguments(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $this->assertEquals([], $cio->getArguments());
    }

    public function testGetArgument(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $this->assertEquals('', $cio->getArgument('foo'));
        $this->assertEquals('bar', $cio->getArgument('foo', 'bar'));
    }

    public function testGetStandardInput(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $this->assertEquals([], $cio->getStandardInput());
    }

    public function testIsInteractive(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $this->assertFalse($cio->isInteractive());
    }

    public function testIsDebug(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $this->assertFalse($cio->isDebug());
    }

    public function testIsVerbose(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $this->assertFalse($cio->isVerbose());
    }

    public function testIsVeryVerbose(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $this->assertFalse($cio->isVeryVerbose());
    }

    public function testWriteError(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $cio->writeError('foo');
        $this->assertTrue(true);
    }

    public function testAsk(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $this->assertEquals('bar', $cio->ask('foo', 'bar'));
    }

    public function testAskConfirmation(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $this->assertEquals(true, $cio->askConfirmation('foo', true));
    }

    public function testAskAndValidate(): void
    {
        $io  = new NullIO();
        $cio = new CollectorIO($io);
        $this->assertTrue(
            $cio->askAndValidate(
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
