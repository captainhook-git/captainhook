<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Action\Cli\Command\Placeholder;

use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Mockery as AppMockery;
use PHPUnit\Framework\TestCase;

class StdInTest extends TestCase
{
    use IOMockery;
    use AppMockery;
    use ConfigMockery;

    public function testStdInValue(): void
    {
        $expected = 'refs/heads/main 9dfa0fa6221d75f48b2dfac359127324bedf8409'
                  . ' refs/heads/main 8309f6e16097754469c485e604900c573bf2c5d8';

        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();
        $io->expects($this->once())->method('getStandardInput')->willReturn([$expected]);

        $placeholder = new StdIn($io, $config, $repo);
        $result      = $placeholder->replacement([]);

        $this->assertEquals(escapeshellarg($expected), $result);
    }

    public function testEmptyStdIn(): void
    {
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();
        $io->method('getStandardInput')->willReturn([]);

        $placeholder = new StdIn($io, $config, $repo);
        $result      = $placeholder->replacement([]);
        $this->assertEquals("''", $result);
    }
}
