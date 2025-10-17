<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Hook;

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Mockery as CHMockery;
use PHPUnit\Framework\TestCase;

class PostCheckoutTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testRunHookEnabled(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();

        $dummy = new DummyRepo(['hooks' => ['post-checkout' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $hookConfig    = $this->createHookConfigMock();
        $actionConfig1 = $this->createActionConfigMock();
        $actionConfig2 = $this->createActionConfigMock();
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())->method('getActions')->willReturn([$actionConfig1, $actionConfig2]);
        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $io->expects($this->atLeast(1))->method('write');

        // Ensure that our actions are processed.
        $actionConfig1->expects($this->atLeast(1))->method('getAction');
        $actionConfig1->expects($this->atLeast(1))->method('getConditions');
        $actionConfig2->expects($this->atLeast(1))->method('getAction');
        $actionConfig2->expects($this->atLeast(1))->method('getConditions');

        $runner = new PostCheckout($io, $config, $repo);
        $runner->run();
    }

    public function testRunHookDisabled(): void
    {
        $io           = $this->createIOMock();
        $config       = $this->createConfigMock();
        $repo         = $this->createRepositoryMock();
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(false);
        $io->expects($this->atLeast(1))->method('write');

        $runner = new PostCheckout($io, $config, $repo);
        $runner->run();
    }
}
