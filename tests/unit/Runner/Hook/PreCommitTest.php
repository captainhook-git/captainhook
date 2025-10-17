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

use CaptainHook\App\Config\Action;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Exception\ActionFailed;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Mockery as CHMockery;
use PHPUnit\Framework\TestCase;

class PreCommitTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testRunHookEnabled(): void
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('not tested on windows');
        }

        $dummy = new DummyRepo(['hooks' => ['pre-commit' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        // fail on first error must be active
        $config = $this->createConfigMock();
        $config->method('failOnFirstError')->willReturn(true);

        $io           = $this->createIOMock();
        $hookConfig   = $this->createHookConfigMock();
        $actionConfig = $this->createActionConfigMock();
        $actionConfig->method('getAction')->willReturn(CH_PATH_FILES . '/bin/success');
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())->method('getActions')->willReturn([$actionConfig]);
        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $io->expects($this->atLeast(1))->method('write');

        $runner = new PreCommit($io, $config, $repo);
        $runner->run();
    }

    public function testRunHookDontFailOnFirstError(): void
    {
        $this->expectException(ActionFailed::class);

        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('not tested on windows');
        }
        $dummy = new DummyRepo(['hooks' => ['pre-commit' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        // we have to create a config that does not fail on first error
        $config              = $this->createConfigMock();
        $config->expects($this->once())->method('failOnFirstError')->willReturn(false);

        $io                  = $this->createIOMock();
        $hookConfig          = $this->createHookConfigMock();
        $actionConfigFail    = $this->createActionConfigMock();
        $actionConfigSuccess = $this->createActionConfigMock();

        // every action has to get executed
        $actionConfigFail->expects($this->atLeastOnce())
                         ->method('getAction')
                         ->willReturn(CH_PATH_FILES . '/bin/failure');

        // so even if the first actions fails this action has to get executed
        $actionConfigSuccess->expects($this->atLeastOnce())
                            ->method('getAction')
                            ->willReturn(CH_PATH_FILES . '/bin/failure');

        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())
                   ->method('getActions')
                   ->willReturn([$actionConfigFail, $actionConfigSuccess]);

        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->method('isHookEnabled')->willReturn(true);
        $io->expects($this->atLeast(1))->method('write');

        $runner = new PreCommit($io, $config, $repo);
        $runner->run();
    }

    public function testRunHookDontFailEvenOnExceptions(): void
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('not tested on windows');
        }
        $dummy = new DummyRepo(['hooks' => ['pre-commit' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $config              = $this->createConfigMock();
        $config->expects($this->once())->method('isFailureAllowed')->willReturn(false);

        $io                  = $this->createIOMock();
        $hookConfig          = $this->createHookConfigMock();
        $actionConfigSuccess = $this->createActionConfigMock();

        // every action has to get executed
        $actionConfigSuccess->expects($this->atLeastOnce())
                            ->method('getAction')
                            ->willReturn(CH_PATH_FILES . '/bin/success');

        // so even if the first actions fails this action has to get executed
        $actionConfigFail = new Action(CH_PATH_FILES . '/bin/failure', [], [], ['allow-failure' => true]);

        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())
                   ->method('getActions')
                   ->willReturn([$actionConfigFail, $actionConfigSuccess]);

        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $io->expects($this->atLeast(1))->method('write');

        $runner = new PreCommit($io, $config, $repo);
        $runner->run();
    }

    public function testRunHookAllowFailureGlobally(): void
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('not tested on windows');
        }
        $dummy = new DummyRepo(['hooks' => ['pre-commit' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        // we have to create a config that does not fail even if errors occur
        $config = $this->createConfigMock();
        $config->expects($this->once())->method('isFailureAllowed')->willReturn(true);

        $io                  = $this->createIOMock();
        $hookConfig          = $this->createHookConfigMock();
        $actionConfigSuccess = $this->createActionConfigMock();

        // every action has to get executed
        $actionConfigSuccess->expects($this->atLeastOnce())
            ->method('getAction')
            ->willReturn(CH_PATH_FILES . '/bin/success');

        // so even if the first actions fails this action has to get executed
        $actionConfigFail = new Action(CH_PATH_FILES . '/bin/failure');

        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())
            ->method('getActions')
            ->willReturn([$actionConfigFail, $actionConfigSuccess]);

        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $io->expects($this->atLeast(1))->method('write');

        $runner = new PreCommit($io, $config, $repo);
        $runner->run();
    }

    public function testRunHookDisabled(): void
    {
        $io           = $this->createIOMock();
        $config       = $this->createConfigMock();
        $repo         = $this->createRepositoryMock();
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(false);
        $io->expects($this->atLeast(1))->method('write');

        $runner = new PreCommit($io, $config, $repo);
        $runner->run();
    }
}
