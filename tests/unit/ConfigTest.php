<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App;

use CaptainHook\App\Config\Action;
use CaptainHook\App\Config\Plugin;
use CaptainHook\App\Plugin\CaptainHook as CaptainHookPlugin;
use Exception;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testItCanBeCreatedWithoutFile(): void
    {
        $config = new Config('./no-config.json');
        $this->assertFalse($config->isLoadedFromFile());
    }

    public function testItCanBeLoadedFromFile(): void
    {
        $config = new Config('valid.json', true);
        $this->assertTrue($config->isLoadedFromFile());
    }

    public function testItCanReturnAllHookConfigs(): void
    {
        $config = new Config('valid.json', true);
        $this->assertNotEmpty($config->getHookConfigs());
    }

    public function testItDoesNotAllowInvalidHookNames(): void
    {
        $this->expectException(Exception::class);
        $config = new Config('./no-config.json');
        $config->getHookConfig('foo');
    }

    public function testItCombinesHooksAndVirtualHookConfigurations(): void
    {
        $config = new Config('./no-config.json');
        $config->getHookConfig('post-rewrite')->setEnabled(true);
        $config->getHookConfig('post-rewrite')->addAction(new Action('echo foo'));
        $config->getHookConfig('post-change')->setEnabled(true);
        $config->getHookConfig('post-change')->addAction(new Action('echo bar'));

        $hookConfig = $config->getHookConfigToExecute('post-rewrite');

        $this->assertCount(2, $hookConfig->getActions());
    }

    public function testItAssumesCwdAsGitDir(): void
    {
        $config = new Config('./no-config.json');
        $this->assertEquals(getcwd() . '/.git', $config->getGitDirectory());
    }

    public function testItCanReturnTheConfigurationPath(): void
    {
        $path   = realpath(__DIR__ . '/../files/config/valid.json');
        $config = new Config($path);

        $this->assertEquals($path, $config->getPath());
    }

    public function testIsHasABootstrapDefault(): void
    {
        $path   = realpath(__DIR__ . '/../files/config/valid.json');
        $config = new Config($path);

        $this->assertEquals('vendor/autoload.php', $config->getBootstrap());
    }

    public function testItCanSetTheBootstrap(): void
    {
        $path   = realpath(__DIR__ . '/../files/config/valid.json');
        $config = new Config($path, true, ['bootstrap' => 'libs/autoload.php']);

        $this->assertEquals('libs/autoload.php', $config->getBootstrap());
    }

    public function testNoFailuresAreAllowedByDefault(): void
    {
        $path   = realpath(__DIR__ . '/../files/config/valid.json');
        $config = new Config($path, true);

        $this->assertFalse($config->isFailureAllowed());
    }

    public function testAllowFailureCanBeChanged(): void
    {
        $path   = realpath(__DIR__ . '/../files/config/valid.json');
        $config = new Config($path, true, ['allow-failure' => true]);

        $this->assertTrue($config->isFailureAllowed());
    }

    public function testColorsAreEnabledByDefault(): void
    {
        $config = new Config('foo.json', true);
        $this->assertTrue($config->useAnsiColors());
    }

    public function testAnsiColorsCanBeDisabled(): void
    {
        $config = new Config('foo.json', true, ['ansi-colors' => false]);
        $this->assertFalse($config->useAnsiColors());
    }

    public function testProvidesAccessToRunMode(): void
    {
        $config = new Config('foo.json', true, ['run-mode' => 'docker', 'run-exec' => 'foo']);
        $this->assertEquals('docker', $config->getRunConfig()->getMode());
    }

    public function testProvidesAccessToRunExec(): void
    {
        $config = new Config('foo.json', true, ['run-mode' => 'docker', 'run-exec' => 'foo']);
        $this->assertEquals('foo', $config->getRunConfig()->getDockerCommand());
    }

    public function testRunPathIsEmptyByDefault(): void
    {
        $config = new Config('foo.json', true, ['run-mode' => 'docker', 'run-exec' => 'foo']);
        $this->assertEquals('', $config->getRunConfig()->getCaptainsPath());
    }

    public function testAllowsCustomSettings(): void
    {
        $config = new Config('foo.json', true, ['custom' => ['foo' => 'foo']]);
        $this->assertEquals(['foo' => 'foo'], $config->getCustomSettings());
    }

    public function testProvidesAccessToRunPath(): void
    {
        $config = new Config('foo.json', true, ['run-mode' => 'docker', 'run-exec' => 'foo', 'run-path' => '/foo']);
        $this->assertEquals('/foo', $config->getRunConfig()->getCaptainsPath());
    }

    public function testFailOnFirstErrorIsTrueByDefault(): void
    {
        $config = new Config('foo.json', true, []);
        $this->assertTrue($config->failOnFirstError());
    }

    public function testFailOnFirstErrorCanBeChanged(): void
    {
        $config = new Config('foo.json', true, ['fail-on-first-error' => false]);
        $this->assertFalse($config->failOnFirstError());
    }

    public function testCanBeExportedToJsonData(): void
    {
        $config = new Config('./no-config.json');
        $json   = $config->getJsonData();

        $this->assertIsArray($json);
        $this->assertIsArray($json['pre-commit']);
        $this->assertIsArray($json['commit-msg']);
        $this->assertIsArray($json['pre-push']);
    }

    public function testCanBeExportedToJsonDataWithSettings(): void
    {
        $config = new Config(
            './no-config.json',
            false,
            ['run-path' => '/usr/local/bin/captainhook', 'verbosity' => 'debug']
        );
        $json   = $config->getJsonData();

        $this->assertIsArray($json);
        $this->assertIsArray($json['config']);
        $this->assertIsArray($json['config']['run']);
        $this->assertIsArray($json['pre-commit']);
        $this->assertIsArray($json['commit-msg']);
        $this->assertIsArray($json['pre-push']);
    }

    public function testGetJsonDataWithoutEmptyConfig(): void
    {
        $config = new Config('foo.json', true, []);
        $json   = $config->getJsonData();

        $this->assertArrayNotHasKey('config', $json);
    }

    public function testGetJsonDataWithConfigSection(): void
    {
        $config = new Config('foo.json', true, ['run-mode' => 'docker', 'run-exec' => 'foo']);
        $json   = $config->getJsonData();

        $this->assertIsArray($json);
        $this->assertIsArray($json['config']);
        $this->assertEquals('foo', $json['config']['run']['exec']);
        $this->assertArrayNotHasKey('plugins', $json);
    }

    public function testGetPluginsReturnsEmptyArray(): void
    {
        $config = new Config('foo.json');

        $this->assertSame([], $config->getPlugins());
    }

    public function testGetPluginsReturnsArrayOfPlugins(): void
    {
        $plugin1 = new class implements CaptainHookPlugin {
        };
        $plugin1Name = get_class($plugin1);

        $plugin2 = new class implements CaptainHookPlugin {
        };
        $plugin2Name = get_class($plugin2);

        $config = new Config('foo.json', true, [
            'plugins' => [
                [
                    'plugin' => $plugin1Name,
                    'options' => [
                        'foo' => 'bar',
                    ],
                ],
                [
                    'plugin' => $plugin2Name,
                ],
            ],
        ]);

        $json = $config->getJsonData();

        $this->assertIsArray($json);
        $this->assertIsArray($json['config']);
        $this->assertIsArray($json['config']['plugins']);
        $this->assertCount(2, $config->getPlugins());
        $this->assertContainsOnlyInstancesOf(Plugin::class, $config->getPlugins());
        $this->assertSame(
            [
                [
                    'plugin' => $plugin1Name,
                    'options' => ['foo' => 'bar'],
                ],
                [
                    'plugin' => $plugin2Name,
                    'options' => [],
                ],
            ],
            $json['config']['plugins']
        );
    }
}
