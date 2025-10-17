<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Config;

use CaptainHook\App\Config;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testOnlyWorksIfConfigIsLoadedFromFile(): void
    {
        $this->expectException(Exception::class);

        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();

        $runner = new Reader($io, $config, $repo);
        $runner->setHook('pre-commit')
               ->run();
    }

    public function testDoesNotAllowInvalidHookNames(): void
    {
        $this->expectException(Exception::class);

        $io     = $this->createIOMock();
        $config = $this->createConfigMock(true);
        $repo   = $this->createRepositoryMock();

        $runner = new Reader($io, $config, $repo);
        $runner->setHook('foo')
               ->run();
    }

    public function testItDisplaysOnlyActions(): void
    {
        $path   = realpath(CH_PATH_FILES . '/config/valid.json');
        $config = Config\Factory::create($path);
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();

        $io->expects($this->exactly(2))->method('write');

        $runner = new Reader($io, $config, $repo);
        $runner->setHook('pre-commit')
               ->display(Reader::OPT_ACTIONS, true)
               ->run();
    }

    public function testItDisplaysActionsAndConditions(): void
    {
        $path   = realpath(CH_PATH_FILES . '/config/valid-with-conditions.json');
        $config = Config\Factory::create($path);
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();

        $io->expects($this->atLeast(3))->method('write');

        $runner = new Reader($io, $config, $repo);
        $runner->setHook('pre-commit')
            ->display(Reader::OPT_ACTIONS, true)
            ->display(Reader::OPT_CONDITIONS, true)
            ->run();
    }

    public function testItDisplaysAll(): void
    {
        $path   = realpath(CH_PATH_FILES . '/config/valid-with-nested-and-conditions.json');
        $config = Config\Factory::create($path);
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();

        $io->expects($this->atLeast(4))->method('write');

        $runner = new Reader($io, $config, $repo);
        $runner->run();
    }

    public function testDisplaysExtended(): void
    {
        $path   = realpath(CH_PATH_FILES . '/config/valid.json');
        $config = Config\Factory::create($path);
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();

        $repo->expects($this->once())->method('hookExists')->willReturn(true);
        $io->expects($this->atLeast(2))->method('write');

        $runner = new Reader($io, $config, $repo);
        $runner->setHook('pre-commit')
            ->display(Reader::OPT_ACTIONS, true)
            ->extensive(true)
            ->run();
    }
}
