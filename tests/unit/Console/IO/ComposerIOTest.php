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

use Composer\IO\IOInterface;
use PHPUnit\Framework\TestCase;

class ComposerIOTest extends TestCase
{
    /**
     * @var \CaptainHook\App\Console\IO;
     */
    private $io;

    protected function setUp(): void
    {
        $mock = $this->getMockBuilder(IOInterface::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $mock->method('isInteractive')->willReturn(false);
        $mock->method('isDebug')->willReturn(false);
        $mock->method('isVerbose')->willReturn(false);
        $mock->method('isVeryVerbose')->willReturn(false);
        $mock->method('ask')->willReturn('bar');
        $mock->method('askConfirmation')->willReturn(true);
        $mock->method('askAndValidate')->willReturn(true);
        $this->io = new ComposerIO($mock);
    }

    protected function tearDown(): void
    {
        $this->io = null;
    }

    public function testIsInteractive(): void
    {
        $this->assertFalse($this->io->isInteractive());
    }

    public function testIsDebug(): void
    {
        $this->assertFalse($this->io->isDebug());
    }

    public function testIsVerbose(): void
    {
        $this->assertFalse($this->io->isVerbose());
    }

    public function testIsVeryVerbose(): void
    {
        $this->assertFalse($this->io->isVeryVerbose());
    }

    public function testWrite(): void
    {
        $this->io->write('foo');

        $this->assertTrue(true);
    }

    public function testWriteError(): void
    {
        $this->io->writeError('foo');

        $this->assertTrue(true);
    }

    public function testAsk(): void
    {
        $this->assertEquals('bar', $this->io->ask('foo', 'bar'));
    }

    public function testAskConfirmation(): void
    {
        $this->assertEquals(true, $this->io->askConfirmation('foo', true));
    }

    public function testAskAndValidate(): void
    {
        $this->assertEquals(
            true,
            $this->io->askAndValidate(
                'foo',
                function () {
                    return true;
                },
                null,
                true
            )
        );
    }
}
