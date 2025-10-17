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

class PostRewriteTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testRunHookEnabled(): void
    {
        $dummy = new DummyRepo(['hooks' => ['post-rewrite' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $io           = $this->createIOMock();
        $config       = $this->createConfigMock();
        $hookConfig   = $this->createHookConfigMock();

        // configure the actually called hook
        $actionConfig = $this->createActionConfigMock();
        $hookConfig->method('getName')->willReturn('post-rewrite');
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())->method('getActions')->willReturn([$actionConfig]);

        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);

        $io->expects($this->atLeast(1))->method('write');

        $runner = new PostRewrite($io, $config, $repo);
        $runner->run();
    }
}
