<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Console\Command\Hook;

use CaptainHook\App\Console\Runtime\Resolver;
use CaptainHook\App\Git\DummyRepo;
use Exception;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class PreCommitTest extends TestCase
{
    public function testExecuteLib(): void
    {
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                '--configuration' => CH_PATH_FILES . '/config/valid.json',
                '--git-directory' => $repo->getGitDir()
            ]
        );

        $cmd = new PreCommit(new Resolver());
        $cmd->run($input, $output);

        $this->assertTrue(true);
    }

    public function testExecuteSkip(): void
    {
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                '--configuration' => CH_PATH_FILES . '/config/valid.json',
                '--git-directory' => $repo->getGitDir()
            ]
        );

        $_SERVER['CAPTAINHOOK_SKIP_HOOKS'] = 1;

        $cmd = new PreCommit(new Resolver());
        $cmd->run($input, $output);

        $this->assertTrue(true);

        $_SERVER['CAPTAINHOOK_SKIP_HOOKS'] = 0;
    }

    public function testExecutePhar(): void
    {
        $resolver = $this->createMock(Resolver::class);
        $resolver->expects($this->atLeast(1))->method('isPharRelease')->willReturn(true);

        $repo     = new DummyRepo();
        $output   = new NullOutput();
        $input    = new ArrayInput(
            [
                '--configuration' => CH_PATH_FILES . '/config/empty.json',
                '--git-directory' => $repo->getGitDir(),
                '--bootstrap'     => '../bootstrap/constant.php'
            ]
        );

        $cmd = new PreCommit($resolver);
        $cmd->run($input, $output);

        $this->assertTrue(true);
        $this->assertTrue(defined('CH_BOOTSTRAP_WORKED'));
    }

    public function testExecutePharBootstrapNotFound(): void
    {
        $resolver = $this->createMock(Resolver::class);
        $resolver->expects($this->atLeast(1))->method('isPharRelease')->willReturn(true);

        $repo     = new DummyRepo();
        $output   = new NullOutput();
        $input    = new ArrayInput(
            [
                '--configuration' => CH_PATH_FILES . '/config/empty.json',
                '--git-directory' => $repo->getGitDir(),
                '--bootstrap'     => '../bootstrap/fail.php'
            ]
        );

        $cmd = new PreCommit($resolver);
        $this->assertEquals(1, $cmd->run($input, $output));
    }

    public function testExecutePharBootstrapNotSet(): void
    {
        $resolver = $this->createMock(Resolver::class);
        $resolver->expects($this->atLeast(1))->method('isPharRelease')->willReturn(true);

        $repo     = new DummyRepo();
        $output   = new NullOutput();
        $input    = new ArrayInput(
            [
                '--configuration' => CH_PATH_FILES . '/config/empty.json',
                '--git-directory' => $repo->getGitDir()
            ]
        );

        $cmd = new PreCommit($resolver);
        $this->assertEquals(0, $cmd->run($input, $output));
    }

    public function testExecuteFailingActionInDebugMode(): void
    {
        $this->expectException(Exception::class);

        $output = $this->createMock(NullOutput::class);
        $output->expects($this->once())->method('isDebug')->willReturn(true);

        $resolver = new Resolver();
        $repo     = new DummyRepo();
        $input    = new ArrayInput(
            [
                '--configuration' => CH_PATH_FILES . '/config/valid-but-failing.json',
                '--git-directory' => $repo->getGitDir(),
                '--bootstrap'     => '../bootstrap/empty.php'
            ]
        );

        $cmd = new PreCommit($resolver);
        $cmd->run($input, $output);

        $this->assertTrue(true);
    }

    public function testExecuteFailingActionInVerboseMode(): void
    {
        $output = $this->createMock(NullOutput::class);
        $output->expects($this->once())->method('isDebug')->willReturn(false);

        $resolver = new Resolver();
        $repo     = new DummyRepo();
        $input    = new ArrayInput(
            [
                '--configuration' => CH_PATH_FILES . '/config/valid-but-failing.json',
                '--git-directory' => $repo->getGitDir(),
                '--bootstrap'     => '../bootstrap/empty.php'
            ]
        );

        $cmd = new PreCommit($resolver);
        $this->assertEquals(1, $cmd->run($input, $output));
    }
}
