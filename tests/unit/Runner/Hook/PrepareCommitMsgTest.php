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

use CaptainHook\App\Config;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Hook\Message\Action\Prepare;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Git\Repository;

class PrepareCommitMsgTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testRunHookEnabled(): void
    {
        $io           = $this->createIOMock();
        $config       = $this->createConfigMock();
        $hookConfig   = $this->createHookConfigMock();
        $actionConfig = $this->createActionConfigMock();
        $actionConfig->method('getAction')->willReturn('\\' . Prepare::class);
        $actionConfig->method('getOptions')->willReturn(new Config\Options(['message' => 'Prepared commit msg']));
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())->method('getActions')->willReturn([$actionConfig]);
        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);

        $configOp = $this->createGitConfigOperator();
        $configOp->method('getSettingSafely')->willReturn('#');
        // setup fake vfs repos directory
        $dummy = new DummyRepo(['config' => '#config', 'hooks' => ['prepare-commit-msg' => '# hook script']]);
        $repo  = new Repository($dummy->getRoot());

        $commitMessageFile = $dummy->getGitDir() . '/prepare-commit-msg';
        file_put_contents($commitMessageFile, 'Commit Message');

        $io->expects($this->atLeast(1))->method('write');
        $io->expects($this->exactly(3))->method('getArgument')->willReturn($commitMessageFile);

        $runner = new PrepareCommitMsg($io, $config, $repo);
        $runner->run();
        $this->assertEquals('Prepared commit msg', file_get_contents($commitMessageFile));
    }

    public function testRunHookNoMessageException(): void
    {
        $this->expectException(Exception::class);

        $dummy = new DummyRepo(['hooks' => ['prepare-commit-msg' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $configOp = $this->createGitConfigOperator();
        $configOp->method('getSettingSafely')->willReturn('#');
        $repo->method('getConfigOperator')->willReturn($configOp);

        $io           = $this->createIOMock();
        $config       = $this->createConfigMock();
        $actionConfig = $this->createActionConfigMock();
        $actionConfig->method('getAction')->willReturn(Prepare::class);
        $actionConfig->method('getOptions')->willReturn(new Config\Options(['message' => 'Prepared commit msg']));
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $io->expects($this->exactly(3))->method('getArgument')->willReturn('');

        $runner = new PrepareCommitMsg($io, $config, $repo);
        $runner->run();
    }
}
