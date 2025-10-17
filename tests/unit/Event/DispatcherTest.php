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

class DispatcherTest extends TestCase
{
    use ConfigMockery;
    use CHMockery;
    use IOMockery;

    public function testSubscribeHandlers(): void
    {
        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $repo    = $this->createRepositoryMock();
        $handler = $this->createEventHandlerMock();

        $dispatcher = new Dispatcher($io, $config, $repo);
        $dispatcher->subscribeHandlers([
            'onHookFailure' => [$handler]
        ]);

        $dispatcher->dispatch('onHookFailure');
    }

    /**
     * Creates an event handler mock
     *
     * @param  bool $executed
     * @return \CaptainHook\App\Event\Handler
     */
    private function createEventHandlerMock(bool $executed = true): Handler
    {
        $handler = $this->getMockBuilder(Handler::class)
                        ->disableOriginalConstructor()
                        ->getMock();

        if ($executed) {
            $handler->expects($this->once())->method('handle');
        }

        return $handler;
    }
}
