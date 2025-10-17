<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\UserInput\EventHandler;

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Event\HookFailed;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use PHPUnit\Framework\TestCase;

class AskConfirmationTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testHandleYes(): void
    {
        $dummy   = new DummyRepo();
        $config  = $this->createConfigMock();
        $io      = $this->createIOMock();
        $repo    = $this->createRepositoryMock($dummy->getRoot());
        $event   = new HookFailed($io, $config, $repo);

        $io->expects($this->once())->method('ask')->willReturn('y');

        $handler = new AskConfirmation('continue?');
        $handler->handle($event);
    }

    public function testHandleNo(): void
    {
        $this->expectException(Exception::class);

        $dummy   = new DummyRepo();
        $config  = $this->createConfigMock();
        $io      = $this->createIOMock();
        $repo    = $this->createRepositoryMock($dummy->getRoot());
        $event   = new HookFailed($io, $config, $repo);

        $io->expects($this->once())->method('ask')->willReturn('n');

        $handler = new AskConfirmation('continue?');
        $handler->handle($event);
    }
}
