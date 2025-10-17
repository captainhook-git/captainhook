<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Config\Setup;

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as CHMockery;
use PHPUnit\Framework\TestCase;

class ExpressTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testConfigureExpress(): void
    {
        $invokedCount = $this->atLeast(6);
        $io           = $this->createIOMock();
        $config       = $this->createConfigMock();
        $config->expects($this->exactly(2))->method('getHookConfig')->willReturn($this->createHookConfigMock());
        $io->expects($invokedCount)
           ->method('ask')
           ->willReturnCallback(function ($parameters) use ($invokedCount) {
               $results = ['y', 'y', 'y', 'phpunit', 'y', 'phpcs'];
               return $results[$invokedCount->numberOfInvocations() - 1] ?? '';
           });

        $setup  = new Express($io);
        $setup->configureHooks($config);
    }
}
