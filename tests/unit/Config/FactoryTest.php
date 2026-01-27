<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Config;

use Exception;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/valid.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
    }

    public function testCustomValuesCanBeDefined(): void
    {
        $config = Factory::create(CH_PATH_FILES . '/config/valid-with-all-settings.json');
        $custom = $config->getCustomSettings();

        $this->assertEquals('bar', $custom['foo']);
    }

    public function testOverwriteConfigSettingsBySettingsConfigFile(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/config-file/captainhook.json'));

        $this->assertEquals('quiet', $config->getVerbosity());
    }

    public function testCreateWithAbsoluteGitDir(): void
    {
        $config = Factory::create(
            realpath(CH_PATH_FILES . '/config/valid.json'),
            ['git-directory' => '/foo']
        );

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
        $this->assertEquals('/foo', $config->getGitDirectory());
    }

    public function testCreateWithInvalidPhpPath(): void
    {
        $this->expectException(Exception::class);

        Factory::create(
            realpath(CH_PATH_FILES . '/config/valid.json'),
            ['php-path' => '/foo/bar/baz']
        );
    }

    public function testCreateWithRelativeGitDir(): void
    {
        $path   = realpath(CH_PATH_FILES . '/config/valid.json');
        $config = Factory::create($path, ['git-directory' => '../.git']);

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
        $this->assertEquals(dirname($path) . DIRECTORY_SEPARATOR . '../.git', $config->getGitDirectory());
    }

    public function testCreateWithRunConfig(): void
    {
        $path   = realpath(CH_PATH_FILES . '/config/valid-run-config-nested.json');
        $config = Factory::create($path);

        $this->assertEquals('./vendor/bin/captainhook', $config->getRunConfig()->getCaptainsPath());
        $this->assertEquals('docker', $config->getRunConfig()->getMode());
        $this->assertEquals('/docker/.git', $config->getRunConfig()->getGitPath());
    }

    public function testCreateWithRunConfigLegacy(): void
    {
        $path   = realpath(CH_PATH_FILES . '/config/valid-run-config-legacy.json');
        $config = Factory::create($path);

        $this->assertEquals('./vendor/bin/captainhook', $config->getRunConfig()->getCaptainsPath());
        $this->assertEquals('docker', $config->getRunConfig()->getMode());
        $this->assertEquals('/docker/.git', $config->getRunConfig()->getGitPath());
    }

    public function testCreateWithConditions(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/valid-with-conditions.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
    }

    public function testCreateWithSettings(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/valid-with-conditions.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->getActions()[0]->isFailureAllowed());
    }

    public function testCreateWithCrazyPHPPath(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/valid-with-strange-settings.json'));

        $this->assertEquals("tests/_files/bin/success foo", $config->getPhpPath());
    }

    public function testCreateWithAllSetting(): void
    {
        $path   = realpath(CH_PATH_FILES . '/config/valid-with-all-settings.json');
        $gitDir = dirname($path) . DIRECTORY_SEPARATOR . '../../../.git';
        $config = Factory::create($path);

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
        $this->assertEquals('verbose', $config->getVerbosity());
        $this->assertEquals($gitDir, $config->getGitDirectory());
        $this->assertFalse($config->useAnsiColors());
        $this->assertEquals('docker', $config->getRunConfig()->getMode());
        $this->assertEquals('docker exec CONTAINER_NAME', $config->getRunConfig()->getDockerCommand());
        $this->assertFalse($config->failOnFirstError());
    }

    public function testCreateWithIncludes(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/valid-with-includes.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(2, $config->getHookConfig('pre-commit')->getActions());
    }

    public function testCreateWithValidNestedIncludes(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/valid-with-nested-includes.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(3, $config->getHookConfig('pre-commit')->getActions());
        $this->assertFalse($config->getHookConfig('pre-push')->isEnabled());
        $this->assertCount(2, $config->getHookConfig('pre-push')->getActions());
    }

    public function testCreateWithInvalidNestedIncludes(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/invalid-with-nested-includes.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(2, $config->getHookConfig('pre-commit')->getActions());
    }

    public function testCreateWithInvalidIncludes(): void
    {
        $this->expectException(Exception::class);
        Factory::create(realpath(CH_PATH_FILES . '/config/valid-with-invalid-includes.json'));
    }

    public function testCreateEmptyWithIncludes(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/empty-with-includes.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
    }

    public function testCreateWithNestedAndConditions(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/valid-with-nested-and-conditions.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
    }

    public function testWithMainConfigurationOverridingInclude(): void
    {
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/valid-with-disabled-action.json'));

        $this->assertFalse($config->getHookConfig('pre-commit')->isEnabled());
    }

    public function testMaxIncludeLevel(): void
    {
        // one of the included files will not be loaded because of the includes-level value of 2
        $config = Factory::create(realpath(CH_PATH_FILES . '/config/valid-with-exceeded-max-include-level.json'));
        // all files have combined 6 pre-commit actions but one should not be loaded
        $this->assertCount(5, $config->getHookConfig('pre-commit')->getActions());
    }
}
