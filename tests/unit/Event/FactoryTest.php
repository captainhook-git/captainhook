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

use Exception;
use PHPUnit\Framework\TestCase;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as CHMockery;

class FactoryTest extends TestCase
{
    use ConfigMockery;
    use CHMockery;
    use IOMockery;

    public function testCreateEvent(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();

        $factory = new Factory($io, $config, $repo);
        $event   = $factory->createEvent('onHookFailure');

        $this->assertInstanceOf(HookFailed::class, $event);
    }

    public function testInvalidEvent(): void
    {
        $this->expectException(Exception::class);

        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();

        $factory = new Factory($io, $config, $repo);
        $event   = $factory->createEvent('invalidEventName');
    }
}
