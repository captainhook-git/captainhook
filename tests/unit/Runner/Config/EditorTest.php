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

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class EditorTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testInvalidHook(): void
    {
        $this->expectException(Exception::class);

        $io     = $this->createIOMock();
        $config = $this->createConfigMock();

        $runner = new Editor($io, $config);
        $runner->setHook('foo')
               ->setChange('EnableHook')
               ->run();
    }

    public function testNoHook(): void
    {
        $this->expectException(Exception::class);

        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $config->expects($this->once())->method('isLoadedFromFile')->willReturn(true);

        $runner = new Editor($io, $config);
        $runner->setChange('EnableHook')
               ->run();
    }

    public function testNoChange(): void
    {
        $this->expectException(Exception::class);

        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $config->expects($this->once())->method('isLoadedFromFile')->willReturn(true);

        $runner = new Editor($io, $config);
        $runner->setHook('pre-commit')
               ->run();
    }

    public function testInvalidChange(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid change requested');

        $io     = $this->createIOMock();
        $config = $this->createConfigMock(true);

        $runner = new Editor($io, $config);
        $runner->setChange('InvalidChange')
               ->setHook('pre-commit')
               ->run();
    }

    public function testMissingHook(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No hook set');

        $io     = $this->createIOMock();
        $config = $this->createConfigMock(true);

        $runner = new Editor($io, $config);
        $runner->setChange('EnableHook')
               ->run();
    }

    public function testMissingChange(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No change set');

        $io     = $this->createIOMock();
        $config = $this->createConfigMock(true);

        $runner = new Editor($io, $config);
        $runner->setHook('pre-commit')
               ->run();
    }

    public function testNoConfiguration()
    {
        $this->expectException(Exception::class);

        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $config->expects($this->once())->method('isLoadedFromFile')->willReturn(false);

        $runner = new Editor($io, $config);
        $runner->setChange('AddAction')
               ->setHook('pre-commit')
               ->run();
    }

    public function testConfigureFileExtend()
    {
        $configDir   = vfsStream::setup('root', null, ['captainhook.json' => '{}']);
        $invocations = $this->atLeast(3);
        $io          = $this->createIOMock();
        $config      = $this->createConfigMock(true, $configDir->url() . '/captainhook.json');
        $config->method('getHookConfig')->willReturn($this->createHookConfigMock());
        $io->expects($invocations)
           ->method('ask')
           ->willReturnCallback(function ($parameters) use ($invocations) {
               $results = ['y', 'y', '\\Foo\\Bar', 'y', 'foo:bar', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n'];
               return $results[$invocations->numberOfInvocations() - 1] ?? '';
           });
        $io->expects($this->once())->method('askAndValidate')->willReturn('foo:bar');

        $runner = new Creator($io, $config);
        $runner->extend(true)
               ->advanced(true)
               ->run();

        $this->assertFileExists($configDir->url() . '/captainhook.json');
    }
}
