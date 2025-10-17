<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Event;

use PHPUnit\Framework\TestCase;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as CHMockery;

class HookFailedTest extends TestCase
{
    use ConfigMockery;
    use CHMockery;
    use IOMockery;

    public function testEvent(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();

        $event  = new HookFailed($io, $config, $repo);

        $this->assertEquals($io, $event->io());
        $this->assertEquals($config, $event->config());
        $this->assertEquals($repo, $event->repository());
    }
}
