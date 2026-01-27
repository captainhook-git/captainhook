<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner;

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Exception\InvalidHookName;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Hook\Mockery as HookMockery;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SebastianFeldmann\Git\Repository;

class UninstallerTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;
    use HookMockery;

    public function testSetInvalidHook(): void
    {
        $this->expectException(InvalidHookName::class);

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock(true);
        $repo     = $this->createRepositoryMock();

        $runner = new Uninstaller($io, $config, $repo);
        $runner->setHook('itDoesNotExist');
    }

    public function testSetHookAndEnabledOnly(): void
    {
        $this->expectException(Exception::class);

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock();

        $runner = new Uninstaller($io, $config, $repo);
        $runner->setHook('pre-push');
        $runner->setOnlyDisabled(true);
    }

    public function testHookUninstallationDeclined(): void
    {
        $fakeRepo = new DummyRepo([
            'config' => '# fake git config',
            'hooks'  => [
                'pre-commit' => '# fake pre-commit hook file',
                'pre-push'   => '# fake pre-push hook file',
            ]
        ]);

        $io     = $this->createIOMock();
        $config = $this->createConfigMock(true);
        $repo   = new Repository($fakeRepo->getRoot());

        $io->expects($this->atLeast(2))->method('ask')->willReturn('n');

        $runner = new Uninstaller($io, $config, $repo);
        $runner->setHook('');
        $runner->run();

        $this->assertFileExists($fakeRepo->getHookDir() . '/pre-commit');
        $this->assertFileExists($fakeRepo->getHookDir() . '/pre-push');
    }

    public function testForcedHookUninstallation(): void
    {
        $fakeRepo = new DummyRepo([
            'config' => '# fake git config',
            'hooks'  => [
                'pre-commit' => '# fake pre-commit hook file',
                'pre-push'   => '# fake pre-push hook file',
            ]
        ]);

        $io     = $this->createIOMock();
        $config = $this->createConfigMock(true);
        $repo   = new Repository($fakeRepo->getRoot());

        $io->expects($this->exactly(0))->method('ask');

        $runner = new Uninstaller($io, $config, $repo);
        $runner->setForce(true);
        $runner->run();

        $this->assertFileDoesNotExist($fakeRepo->getHookDir() . '/pre-commit');
        $this->assertFileDoesNotExist($fakeRepo->getHookDir() . '/pre-push');
    }

    public function testRemoveHook(): void
    {
        $fakeRepo = new DummyRepo(
            [
                'config' => '# fake git config',
                'hooks'  => [
                    'pre-commit' => '# fake pre-commit file',
                    'pre-push'   => '# fake pre-push file',
                ]
            ]
        );

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock(true);
        $repo     = new Repository($fakeRepo->getRoot());

        $runner = new Uninstaller($io, $config, $repo);
        $runner->setHook('pre-commit');
        $runner->run();

        $this->assertFileDoesNotExist($fakeRepo->getHookDir() . '/pre-commit');
        $this->assertFileExists($fakeRepo->getHookDir() . '/pre-push');
    }

    public function testRemoveOnlyDisabled(): void
    {
        $fakeRepo = new DummyRepo(
            [
                'config' => '# fake git config',
                'hooks'  => [
                    'pre-commit' => '# fake pre-commit file',
                    'pre-push'   => '# fake pre-push file',
                ]
            ]
        );

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock(true);
        $repo     = new Repository($fakeRepo->getRoot());

        $config->method('isHookEnabled')->willReturnCallback(
            function (string $hook): bool {
                return $hook === 'pre-push';
            }
        );

        $runner = new Uninstaller($io, $config, $repo);
        $runner->setOnlyDisabled(true);
        $runner->setForce(true);
        $runner->run();

        $this->assertFileDoesNotExist($fakeRepo->getHookDir() . '/pre-commit');
        $this->assertFileExists($fakeRepo->getHookDir() . '/pre-push');
    }

    public function testMoveExistingHook(): void
    {
        $fakeRepo = new DummyRepo(
            [
                'config' => '# fake git config',
                'hooks'  => [
                    'post-merge' => '# fake pre-commit file',
                    'pre-push'   => '# fake pre-push file',
                ]
            ]
        );

        $io     = $this->createIOMock();
        $config = $this->createConfigMock(true, $fakeRepo->getRoot() . '/captainhook.json');
        $repo   = new Repository($fakeRepo->getRoot());

        $runner = new Uninstaller($io, $config, $repo);
        $runner->setHook('post-merge')
               ->setMoveExistingTo('foo/bar/')
               ->run();

        $this->assertFileDoesNotExist($fakeRepo->getHookDir() . '/post-merge');
        $this->assertFileExists($fakeRepo->getRoot() . '/foo/bar/post-merge');
    }

    public function testMoveExistingHookTargetIsFile(): void
    {
        $this->expectException(RuntimeException::class);

        $fakeRepo = new DummyRepo(
            // git repo
            [
                'config' => '# fake git config',
                'hooks'  => [
                    'pre-commit' => '# fake pre-commit file',
                    'pre-push'   => '# fake pre-push file',
                ]
            ],
            // files
            [
                'foo' => '# some random file'
            ]
        );

        $io     = $this->createIOMock();
        $config = $this->createConfigMock(true, $fakeRepo->getRoot() . '/captainhook.json');
        $repo   = new Repository($fakeRepo->getRoot());

        $runner = new Uninstaller($io, $config, $repo);
        $runner->setHook('pre-commit')
               ->setMoveExistingTo('foo')
               ->run();
    }

    public function testMoveExistingHookWhenMoveExistingIsAnAbsolutePath(): void
    {
        $virtualFs = vfsStream::setup();

        $fakeRepo = new DummyRepo(
            // git repo
            [
                'config' => '# fake git config',
                'hooks'  => [
                    'pre-commit' => '# fake pre-commit file',
                    'pre-push'   => '# fake pre-push file',
                ]
            ],
            // files
            [
                'foo' => []
            ]
        );

        $io     = $this->createIOMock();
        $config = $this->createConfigMock(true, $fakeRepo->getRoot() . '/captainhook.json');
        $repo   = new Repository($fakeRepo->getRoot());

        $runner = new Uninstaller($io, $config, $repo);
        $runner->setHook('pre-commit')
               ->setMoveExistingTo($virtualFs->url() . '/foo/bar')
               ->run();

        $this->assertFileDoesNotExist($fakeRepo->getHookDir() . '/pre-commit');
        $this->assertFileExists($fakeRepo->getRoot() . '/foo/bar/pre-commit');
    }
}
