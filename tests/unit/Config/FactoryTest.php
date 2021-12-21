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
    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreate(): void
    {
        $config = Factory::create(realpath(__DIR__ . '/../../files/config/valid.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testOverwriteConfigSettingsBySettingsConfigFile(): void
    {
        $config = Factory::create(realpath(__DIR__ . '/../../files/config/config-file/captainhook.json'));

        $this->assertEquals('quiet', $config->getVerbosity());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreateWithAbsoluteGitDir(): void
    {
        $config = Factory::create(
            realpath(__DIR__ . '/../../files/config/valid.json'),
            ['git-directory' => '/foo']
        );

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
        $this->assertEquals('/foo', $config->getGitDirectory());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreateWithRelativeGitDir(): void
    {
        $path   = realpath(__DIR__ . '/../../files/config/valid.json');
        $config = Factory::create($path, ['git-directory' => '../.git']);

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
        $this->assertEquals(dirname($path) . DIRECTORY_SEPARATOR . '../.git', $config->getGitDirectory());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreateWithConditions(): void
    {
        $config = Factory::create(realpath(__DIR__ . '/../../files/config/valid-with-conditions.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreateWithAllSetting(): void
    {
        $path   = realpath(__DIR__ . '/../../files/config/valid-with-all-settings.json');
        $gitDir = dirname($path) . DIRECTORY_SEPARATOR . '../../../.git';
        $config = Factory::create($path);

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
        $this->assertEquals('verbose', $config->getVerbosity());
        $this->assertEquals($gitDir, $config->getGitDirectory());
        $this->assertEquals(false, $config->useAnsiColors());
        $this->assertEquals('docker', $config->getRunMode());
        $this->assertEquals('docker exec CONTAINER_NAME', $config->getRunExec());
        $this->assertEquals(false, $config->failOnFirstError());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreateWithIncludes(): void
    {
        $config = Factory::create(realpath(__DIR__ . '/../../files/config/valid-with-includes.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(2, $config->getHookConfig('pre-commit')->getActions());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreateWithValidNestedIncludes(): void
    {
        $config = Factory::create(realpath(__DIR__ . '/../../files/config/valid-with-nested-includes.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(3, $config->getHookConfig('pre-commit')->getActions());
        $this->assertFalse($config->getHookConfig('pre-push')->isEnabled());
        $this->assertCount(2, $config->getHookConfig('pre-push')->getActions());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreateWithInvalidNestedIncludes(): void
    {
        $config = Factory::create(realpath(__DIR__ . '/../../files/config/invalid-with-nested-includes.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(2, $config->getHookConfig('pre-commit')->getActions());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreateWithInvalidIncludes(): void
    {
        $this->expectException(Exception::class);
        Factory::create(realpath(__DIR__ . '/../../files/config/valid-with-invalid-includes.json'));
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreateEmptyWithIncludes(): void
    {
        $config = Factory::create(realpath(__DIR__ . '/../../files/config/empty-with-includes.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testCreateWithNestedAndConditions(): void
    {
        $config = Factory::create(realpath(__DIR__ . '/../../files/config/valid-with-nested-and-conditions.json'));

        $this->assertTrue($config->getHookConfig('pre-commit')->isEnabled());
        $this->assertCount(1, $config->getHookConfig('pre-commit')->getActions());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testWithMainConfigurationOverridingInclude(): void
    {
        $config = Factory::create(realpath(__DIR__ . '/../../files/config/valid-with-disabled-action.json'));

        $this->assertFalse($config->getHookConfig('pre-commit')->isEnabled());
    }

    /**
     * Tests Factory::create
     *
     * @throws \Exception
     */
    public function testMaxIncludeLevel(): void
    {
        // one of the included files will not be loaded because of the includes-level value of 2
        $config = Factory::create(realpath(__DIR__ . '/../../files/config/valid-with-exceeded-max-include-level.json'));
        // all files have combined 6 pre-commit actions but one should not be loaded
        $this->assertCount(5, $config->getHookConfig('pre-commit')->getActions());
    }
}
