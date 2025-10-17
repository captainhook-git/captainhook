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

class ArgTest extends TestCase
{
    use IOMockery;
    use AppMockery;
    use ConfigMockery;

    public function testArgValue(): void
    {
        $expected = '.git/EDIT.msg';

        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();
        $io->expects($this->once())->method('getArgument')->with('message-file')->willReturn($expected);

        $placeholder = new Arg($io, $config, $repo);
        $result      = $placeholder->replacement(['value-of' => 'MESSAGE_FILE']);

        $this->assertEquals($expected, $result);
    }

    public function testNoValueOf(): void
    {
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();
        $io->method('getArgument')->willReturn('');

        $placeholder = new Arg($io, $config, $repo);
        $result      = $placeholder->replacement([]);

        $this->assertEquals('', $result);
    }

    public function testDefault(): void
    {
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();
        $io->method('getArgument')->with('message-file', 'my-default')->willReturn('my-default');

        $placeholder = new Arg($io, $config, $repo);
        $result      = $placeholder->replacement(['value-of' => 'MESSAGE_FILE', 'default' => 'my-default']);

        $this->assertEquals('my-default', $result);
    }
}
