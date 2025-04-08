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
use Exception;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Git\CommitMessage as GitCommitMessage;
use SebastianFeldmann\Git\Operator\Config as ConfigOperator;

/**
 * Class CommitMsgTest
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 3.1.0
 */
class CommitMsgTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    /**
     * Tests CommitMsg::run
     */
    public function testRunHookEnabled(): void
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('not tested on windows');
        }

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $configOp = $this->createMock(ConfigOperator::class);
        $configOp->expects($this->once())->method('getSettingSafely')->willReturn('#');

        $dummy = new DummyRepo(['hooks' => ['commit-msg' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());
        $repo->expects($this->once())->method('getConfigOperator')->willReturn($configOp);

        $hookConfig   = $this->createHookConfigMock();
        $actionConfig = $this->createActionConfigMock();
        $actionConfig->method('getAction')->willReturn(CH_PATH_FILES . '/bin/success');
        $hookConfig->expects($this->once())->method('getActions')->willReturn([$actionConfig]);
        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $io->expects($this->atLeast(1))->method('write');
        $io->expects($this->once())->method('getArgument')->willReturn(CH_PATH_FILES . '/git/message/valid.txt');

        $runner = new CommitMsg($io, $config, $repo);
        $runner->run();
    }

    /**
     * Tests CommitMsg::run
     */
    public function testRunHookSkippedBecauseOfFixup(): void
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('not tested on windows');
        }

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $configOp = $this->createMock(ConfigOperator::class);
        $configOp->expects($this->once())->method('getSettingSafely')->willReturn('#');

        $dummy = new DummyRepo(['hooks' => ['commit-msg' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());
        $repo->expects($this->once())->method('getConfigOperator')->willReturn($configOp);
        $repo->expects($this->once())->method('getCommitMsg')->willReturn(new GitCommitMessage('fixup! foo', '#'));

        $hookConfig   = $this->createHookConfigMock();
        $actionConfig = $this->createActionConfigMock();
        $actionConfig->method('getAction')->willReturn(CH_PATH_FILES . '/bin/success');
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->method('getActions')->willReturn([$actionConfig]);
        $config->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $io->expects($this->atLeast(1))->method('write');
        $io->expects($this->once())->method('getArgument')->willReturn(CH_PATH_FILES . '/git/message/valid.txt');

        $runner = new CommitMsg($io, $config, $repo);
        $runner->run();
    }

    /**
     * Tests CommitMsg::run
     *
     * @throws \Exception
     */
    public function testRunWithoutCommitMsgFile(): void
    {
        $this->expectException(Exception::class);

        $io         = $this->createIOMock();
        $config     = $this->createConfigMock();
        $hookConfig = $this->createHookConfigMock();

        $dummy = new DummyRepo(['hooks' => ['commit-msg' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $configOp     = $this->createGitConfigOperator();
        $actionConfig = $this->createActionConfigMock();
        $actionConfig->method('getAction')->willReturn(CH_PATH_FILES . '/bin/success');
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->method('getActions')->willReturn([$actionConfig]);
        $config->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $configOp->method('getSettingSafely')->willReturn('#');
        $repo->method('getConfigOperator')->willReturn($configOp);
        $io->expects($this->once())->method('getArgument')->willReturn('');

        $runner = new CommitMsg($io, $config, $repo);
        $runner->run();
    }
}
