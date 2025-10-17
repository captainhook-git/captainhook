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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use PHPUnit\Framework\TestCase;

class PostCommitTest extends TestCase
{
    public function testExecuteWithRelativeConfigPath(): void
    {
        $cwd = getcwd();
        chdir(CH_PATH_FILES);

        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                '--configuration' => './config/valid.json',
                '--git-directory' => $repo->getGitDir()
            ]
        );

        $cmd = new PostCommit(new Resolver());
        $cmd->run($input, $output);

        chdir($cwd);

        $this->assertTrue(true);
    }

    public function testMissingBootstrapHandling(): void
    {
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                '--configuration' => CH_PATH_FILES . '/config/valid.json',
                '--git-directory' => $repo->getGitDir(),
                '--bootstrap'     => 'foo/bar/baz.php',
            ]
        );

        $resolver = $this->getMockBuilder(Resolver::class)
                         ->disableOriginalConstructor()
                         ->getMock();
        $resolver->method('isPharRelease')->willReturn(true);

        $cmd = new PostCommit($resolver);
        $this->assertEquals(1, $cmd->run($input, $output));
    }

    public function testCrashingBootstrapHandling(): void
    {
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $this->markTestSkipped(
                'Only available on PHP 8.0 and higher'
            );
        }
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                '--configuration' => CH_PATH_FILES . '/config/valid.json',
                '--git-directory' => $repo->getGitDir(),
                '--bootstrap'     => '../bootstrap/crash.php',
            ]
        );

        $resolver = $this->getMockBuilder(Resolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resolver->method('isPharRelease')->willReturn(true);

        $cmd = new PostCommit($resolver);
        $this->assertEquals(1, $cmd->run($input, $output));
    }
}
