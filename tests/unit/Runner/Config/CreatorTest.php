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
use CaptainHook\App\Config;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class CreatorTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    /**
     * Tests Creator::run
     */
    public function testFailConfigFileExists(): void
    {
        $this->expectException(Exception::class);

        $config = $this->createConfigMock(true);
        $io     = $this->createIOMock();
        $io->method('ask')->will(
            $this->onConsecutiveCalls(
                ...['y', 'y', '\\Foo\\Bar', 'y', 'foo:bar', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n']
            )
        );

        $runner = new Creator($io, $config);
        $runner->advanced(true)
               ->run();
    }

    /**
     * Tests Creator::run
     *
     * Check if a previously defined configuration will not be deleted if we extend the configuration.
     *
     * @throws \Exception
     */
    public function testConfigureFileExtend(): void
    {
        $configFileContentBefore = '{"pre-commit": {"enabled": false,"actions": [{"action": "phpunit"}]}}';

        $configDir  = vfsStream::setup('root', null, ['captainhook.json' => $configFileContentBefore]);
        $configFile = $configDir->url() . '/captainhook.json';
        $config     = Config\Factory::create($configFile);

        $io = $this->createIOMock();
        $io->method('ask')->will(
            $this->onConsecutiveCalls(
                ...['y', 'y', '\\Foo\\Bar', 'y', 'foo:bar', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n']
            )
        );
        $io->expects($this->once())->method('askAndValidate')->willReturn('foo:bar');

        $runner = new Creator($io, $config);
        $runner->extend(true)
               ->advanced(true)
               ->run();

        $configFileContentAfter = $configDir->getChild('captainhook.json')->getContent();
        $this->assertFileExists($configFile);
        $this->assertStringContainsString('pre-commit', $configFileContentAfter);
        $this->assertStringContainsString('pre-push', $configFileContentAfter);
        $this->assertStringContainsString('phpunit', $configFileContentAfter);
    }
}
