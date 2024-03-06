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

class InstallerTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;
    use HookMockery;

    /**
     * Tests Installer::setHook
     *
     * @throws \CaptainHook\App\Exception\InvalidHookName
     */
    public function testSetInvalidHook(): void
    {
        $this->expectException(InvalidHookName::class);

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock();
        $template = $this->createTemplateMock();

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('itDoNotExist');
    }

    /**
     * Tests Installer::setHook
     *
     * @throws \CaptainHook\App\Exception\InvalidHookName
     */
    public function testSetMultipleInvalidHooks(): void
    {
        $this->expectException(InvalidHookName::class);

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock();
        $template = $this->createTemplateMock();

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('itDoNotExist1,itDoNotExist2,itDontExist3');
    }

    /**
     * Tests Installer::setHook
     *
     * @throws \CaptainHook\App\Exception\InvalidHookName
     */
    public function testSetHookAndEnabledOnly(): void
    {
        $this->expectException(Exception::class);

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock();
        $template = $this->createTemplateMock();

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setOnlyEnabled(true);
        $runner->setHook('pre-push');
    }

    /**
     * Tests Installer::setHook
     *
     * @throws \CaptainHook\App\Exception\InvalidHookName
     */
    public function testMoveAfterSkippingFail(): void
    {
        $this->expectException(RuntimeException::class);

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock();
        $template = $this->createTemplateMock();

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('pre-commit');
        $runner->setSkipExisting(true);
        $runner->setMoveExistingTo('/tmp/');
    }

    /**
     * Tests Installer::setHook
     *
     * @throws \CaptainHook\App\Exception\InvalidHookName
     */
    public function testSkipAfterMovingFail(): void
    {
        $this->expectException(RuntimeException::class);

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock();
        $template = $this->createTemplateMock();

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('pre-commit');
        $runner->setMoveExistingTo('/tmp/');
        $runner->setSkipExisting(true);
    }

    /**
     * Tests Installer::run
     */
    public function testHookInstallationDeclined(): void
    {
        $fakeRepo = new DummyRepo();

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock($fakeRepo->getRoot());
        $template = $this->createTemplateMock();

        $io->expects($this->atLeast(5))->method('ask')->willReturn('n');

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('');
        $runner->run();

        $this->assertFileDoesNotExist($fakeRepo->getHookDir() . '/pre-commit');
        $this->assertFileDoesNotExist($fakeRepo->getHookDir() . '/pre-push');
    }

    /**
     * Tests Installer::run
     */
    public function testWriteHook(): void
    {
        $fakeRepo = new DummyRepo();

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock($fakeRepo->getRoot());
        $template = $this->createTemplateMock();

        $template->expects($this->once())
                 ->method('getCode')
                 ->with('pre-commit')
                 ->willReturn('');

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('pre-commit');
        $runner->run();

        $this->assertFileExists($fakeRepo->getHookDir() . '/pre-commit');
    }

    /**
     * Tests Installer::run
     */
    public function testWriteMultipleHooks(): void
    {
        $fakeRepo = new DummyRepo();

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock($fakeRepo->getRoot());
        $template = $this->createTemplateMock();

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('pre-commit,pre-push,post-checkout');
        $runner->run();

        $this->assertFileExists($fakeRepo->getHookDir() . '/pre-commit');
        $this->assertFileExists($fakeRepo->getHookDir() . '/pre-push');
        $this->assertFileExists($fakeRepo->getHookDir() . '/post-checkout');
    }

    /**
     * Tests Installer::run
     */
    public function testMoveExistingHook(): void
    {
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

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock(true, $fakeRepo->getRoot() . '/captainhook.json');
        $template = $this->createTemplateMock();
        $repo     = new Repository($fakeRepo->getRoot());

        $template->expects($this->once())
                 ->method('getCode')
                 ->with('pre-commit')
                 ->willReturn('');

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('pre-commit')
               ->setMoveExistingTo('foo/bar/')
               ->run();

        $this->assertFileExists($fakeRepo->getHookDir() . '/pre-commit');
        $this->assertFileExists($fakeRepo->getRoot() . '/foo/bar/pre-commit');
    }

    /**
     * Tests Installer::run
     */
    public function testMoveNotExistingHook(): void
    {
        $fakeRepo = new DummyRepo(
            // git repo
            [
                'config' => '# fake git config',
                'hooks'  => [
                    'pre-push' => '# fake pre-push file',
                ]
            ],
            // files
            [
                'foo' => []
            ]
        );

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock(true, $fakeRepo->getRoot() . '/captainhook.json');
        $template = $this->createTemplateMock();
        $repo     = new Repository($fakeRepo->getRoot());

        $template->expects($this->once())
                 ->method('getCode')
                 ->with('pre-commit')
                 ->willReturn('');

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('pre-commit')
               ->setMoveExistingTo('foo/bar/')
               ->run();

        $this->assertFileExists($fakeRepo->getHookDir() . '/pre-commit');
    }

    /**
     * Tests Installer::run
     */
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

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock(true, $fakeRepo->getRoot() . '/captainhook.json');
        $template = $this->createTemplateMock();
        $repo     = new Repository($fakeRepo->getRoot());

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('pre-commit')
               ->setMoveExistingTo('foo')
               ->run();
    }

    /**
     * Tests Installer::writeHookFile
     */
    public function testSkipExisting(): void
    {
        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock();
        $template = $this->createTemplateMock();

        $io->expects($this->atLeast(1))->method('write');
        $repo->expects($this->once())->method('hookExists')->willReturn(true);

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setSkipExisting(true);
        $runner->setHook('pre-commit');
        $runner->run();
    }

    /**
     * Tests Installer::writeHookFile
     */
    public function testOnlyEnabledAndHooksToHandle(): void
    {
        $this->expectException(Exception::class);

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock();
        $template = $this->createTemplateMock();

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('pre-commit');
        $runner->setOnlyEnabled(true);
    }

    /**
     * Tests Installer::writeHookFile
     */
    public function testDeclineOverwrite(): void
    {
        $io       = $this->createIOMock();
        $config   = $this->createConfigMock();
        $repo     = $this->createRepositoryMock();
        $template = $this->createTemplateMock();

        $io->expects($this->once())->method('ask')->willReturn('n');
        $repo->expects($this->once())->method('hookExists')->willReturn(true);

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('pre-commit');
        $runner->run();
    }

    public function testMoveExistingHookWhenMoveExistingIsAnAbsolutePath(): void
    {
        $virtualFs = vfsStream::setup('root');

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

        $io       = $this->createIOMock();
        $config   = $this->createConfigMock(true, $fakeRepo->getRoot() . '/captainhook.json');
        $template = $this->createTemplateMock();
        $repo     = new Repository($fakeRepo->getRoot());

        $template->expects($this->once())
            ->method('getCode')
            ->with('pre-commit')
            ->willReturn('');

        $runner = new Installer($io, $config, $repo, $template);
        $runner->setHook('pre-commit')
            ->setMoveExistingTo($virtualFs->url() . '/foo/bar')
            ->run();

        $this->assertFileExists($fakeRepo->getHookDir() . '/pre-commit');
        $this->assertFileExists($fakeRepo->getRoot() . '/foo/bar/pre-commit');
    }
}
