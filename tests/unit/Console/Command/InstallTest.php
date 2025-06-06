<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Console\Command;

use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Console\Runtime\Resolver;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Hook\Template;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class InstallTest extends TestCase
{
    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testFailMissingConfig(): void
    {
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => 'pre-commit',
                '--configuration' => 'foo',
                '--git-directory' => 'bar'
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $code    = $install->run($input, $output);

        $this->assertEquals(1, $code);
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testFailInvalidRepository(): void
    {
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => 'pre-commit',
                '--configuration' => CH_PATH_FILES . '/config/valid.json',
                '--git-directory' => 'bar/.git'
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $install->setIO(new NullIO());
        $code = $install->run($input, $output);

        $this->assertEquals(1, $code);
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testFailMissingRunExecOption(): void
    {
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => 'pre-commit',
                '--configuration' => CH_PATH_FILES . '/template/captainhook.json',
                '--git-directory' => $repo->getGitDir(),
                '--run-mode'      => Template::DOCKER
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $code    = $install->run($input, $output);

        $this->assertEquals(1, $code);
    }


    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallPreCommitHook(): void
    {
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => 'pre-commit',
                '--configuration' => CH_PATH_FILES . '/template/captainhook.json',
                '--git-directory' => $repo->getGitDir()
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $install->run($input, $output);

        $this->assertTrue($repo->hookExists('pre-commit'));
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallMultipleHooks(): void
    {
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => 'pre-commit,pre-push,post-checkout',
                '--configuration' => CH_PATH_FILES . '/template/captainhook.json',
                '--git-directory' => $repo->getGitDir(),
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $install->run($input, $output);

        $this->assertTrue($repo->hookExists('pre-commit'));
        $this->assertTrue($repo->hookExists('pre-push'));
        $this->assertTrue($repo->hookExists('post-checkout'));
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallMultipleHooksWithSpacesAfterAndBetweenSeparator(): void
    {
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => ' pre-commit , pre-push , post-checkout, post-commit',
                '--configuration' => CH_PATH_FILES . '/template/captainhook.json',
                '--git-directory' => $repo->getGitDir(),
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $install->run($input, $output);

        $this->assertTrue($repo->hookExists('pre-commit'));
        $this->assertTrue($repo->hookExists('pre-push'));
        $this->assertTrue($repo->hookExists('post-checkout'));
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallMultipleHooksWithOneWrong(): void
    {
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => 'pre-commit,pre-push,post-checkout,something-wrong',
                '--configuration' => CH_PATH_FILES . '/template/captainhook.json',
                '--git-directory' => $repo->getGitDir(),
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $code    = $install->run($input, $output);

        $this->assertEquals(1, $code);
    }


    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallMultipleHooksWithMultipleWrong(): void
    {
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => 'pre-commit,pre-push,post-checkout,something-wrong1,something-wrong2',
                '--configuration' => CH_PATH_FILES . '/template/captainhook.json',
                '--git-directory' => $repo->getGitDir(),
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $code    = $install->run($input, $output);

        $this->assertEquals(1, $code);
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallOnlyEnabled(): void
    {
        $repo = new DummyRepo();
        $output = new NullOutput();
        $input = new ArrayInput(
            [
                '--only-enabled' => true,
                '--force' => true,
                '--configuration' => CH_PATH_FILES . '/template/captainhook.json',
                '--git-directory' => $repo->getGitDir()
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $install->run($input, $output);

        $this->assertTrue($repo->hookExists('prepare-commit-msg'));
        $this->assertTrue($repo->hookExists('commit-msg'));
        $this->assertTrue($repo->hookExists('pre-commit'));
        $this->assertFalse($repo->hookExists('pre-push'));
        $this->assertFalse($repo->hookExists('post-commit'));
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallOnlyEnabledOnlyVirtual(): void
    {
        $repo = new DummyRepo();
        $output = new NullOutput();
        $input = new ArrayInput(
            [
                '--only-enabled' => true,
                '--force' => true,
                '--configuration' => CH_PATH_FILES . '/template/captainhook-post-change.json',
                '--git-directory' => $repo->getGitDir()
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $install->run($input, $output);

        $this->assertTrue($repo->hookExists('post-checkout'));
        $this->assertTrue($repo->hookExists('post-merge'));
        $this->assertTrue($repo->hookExists('post-rewrite'));
        $this->assertFalse($repo->hookExists('pre-commit'));
        $this->assertFalse($repo->hookExists('pre-push'));
        $this->assertFalse($repo->hookExists('post-commit'));
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallOnlyEnabledNotOnlyVirtual(): void
    {
        $repo = new DummyRepo();
        $output = new NullOutput();
        $input = new ArrayInput(
            [
                '--only-enabled' => true,
                '--force' => true,
                '--configuration' => CH_PATH_FILES . '/template/captainhook-post-change-pre-commit.json',
                '--git-directory' => $repo->getGitDir()
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $install->run($input, $output);

        $this->assertTrue($repo->hookExists('post-checkout'));
        $this->assertTrue($repo->hookExists('post-merge'));
        $this->assertTrue($repo->hookExists('post-rewrite'));
        $this->assertTrue($repo->hookExists('pre-commit'));
        $this->assertFalse($repo->hookExists('pre-push'));
        $this->assertFalse($repo->hookExists('post-commit'));
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallOnlyEnabledNotOnlyVirtualOverlaps(): void
    {
        $repo = new DummyRepo();
        $output = new NullOutput();
        $input = new ArrayInput(
            [
                '--only-enabled' => true,
                '--force' => true,
                '--configuration' => CH_PATH_FILES . '/template/captainhook-post-change-post-merge.json',
                '--git-directory' => $repo->getGitDir()
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $install->run($input, $output);

        $this->assertTrue($repo->hookExists('post-checkout'));
        $this->assertTrue($repo->hookExists('post-merge'));
        $this->assertTrue($repo->hookExists('post-rewrite'));
        $this->assertFalse($repo->hookExists('pre-commit'));
        $this->assertFalse($repo->hookExists('pre-push'));
        $this->assertFalse($repo->hookExists('post-commit'));
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallOnlyEnabledNotOnlyVirtualOverlapsDisabled(): void
    {
        $repo = new DummyRepo();
        $output = new NullOutput();
        $input = new ArrayInput(
            [
                '--only-enabled' => true,
                '--force' => true,
                '--configuration' => CH_PATH_FILES . '/template/captainhook-post-change-post-merge-disabled.json',
                '--git-directory' => $repo->getGitDir()
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $install->run($input, $output);

        $this->assertTrue($repo->hookExists('post-checkout'));
        $this->assertTrue($repo->hookExists('post-merge'));
        $this->assertTrue($repo->hookExists('post-rewrite'));
        $this->assertFalse($repo->hookExists('pre-commit'));
        $this->assertFalse($repo->hookExists('pre-push'));
        $this->assertFalse($repo->hookExists('post-commit'));
    }

    /**
     * Tests Install::run
     *
     * @throws \Exception
     */
    public function testInstallOnlyEnabledAndHook(): void
    {
        $repo = new DummyRepo();
        $output = new NullOutput();
        $input = new ArrayInput(
            [
                'hook' => 'pre-commit',
                '--only-enabled' => true,
                '--configuration' => CH_PATH_FILES . '/template/captainhook.json',
                '--git-directory' => $repo->getGitDir(),
            ]
        );

        $install = new Install(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $code    = $install->run($input, $output);

        $this->assertEquals(1, $code);
    }
}
