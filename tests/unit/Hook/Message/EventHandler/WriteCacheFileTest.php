<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Message\EventHandler;

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Event\HookFailed;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Mockery as CHMockery;
use PHPUnit\Framework\TestCase;

class WriteCacheFileTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testHandle(): void
    {
        $dummy   = new DummyRepo();
        $config  = $this->createConfigMock();
        $io      = $this->createIOMock();
        $repo    = $this->createRepositoryMock($dummy->getRoot());
        $event   = new HookFailed($io, $config, $repo);

        $handler = new WriteCacheFile('.git/TEMP.FILE');
        $handler->handle($event);

        $this->assertTrue(file_exists($dummy->getGitDir() . '/TEMP.FILE'));
    }
}
