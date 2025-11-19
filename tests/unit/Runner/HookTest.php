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

use CaptainHook\App\Config;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Console\IO\TestIO;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Hook\Restriction;
use CaptainHook\App\Hooks;
use CaptainHook\App\Mockery as CHMockery;
use CaptainHook\App\Plugin\DummyConstrainedPlugin;
use CaptainHook\App\Plugin\DummyConstrainedHookPlugin;
use CaptainHook\App\Plugin\DummyConstrainedHookPluginAlt;
use CaptainHook\App\Plugin\DummyPlugin;
use CaptainHook\App\Plugin\DummyHookPlugin;
use CaptainHook\App\Plugin\DummyHookPluginSkipsActions;
use Exception;
use PHPUnit\Framework\TestCase;

class HookTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    protected function setUp(): void
    {
        // Ensure the static properties on dummy plugins are all set to their defaults.
        DummyHookPlugin::$beforeHookCalled = 0;
        DummyHookPlugin::$beforeActionCalled = 0;
        DummyHookPlugin::$afterActionCalled = 0;
        DummyHookPlugin::$afterHookCalled = 0;
        DummyHookPluginSkipsActions::$skipStartIn = 'beforeHook';
        DummyHookPluginSkipsActions::$skipStartAt = 1;
        DummyConstrainedHookPlugin::$restriction = null;
        DummyConstrainedHookPluginAlt::$restriction = null;
        DummyConstrainedPlugin::$restriction = null;
    }

    protected function tearDown(): void
    {
        // Reset the static properties on dummy plugins to their defaults.
        DummyHookPlugin::$beforeHookCalled = 0;
        DummyHookPlugin::$beforeActionCalled = 0;
        DummyHookPlugin::$afterActionCalled = 0;
        DummyHookPlugin::$afterHookCalled = 0;
        DummyHookPluginSkipsActions::$skipStartIn = 'beforeHook';
        DummyHookPluginSkipsActions::$skipStartAt = 1;
        DummyConstrainedHookPlugin::$restriction = null;
        DummyConstrainedHookPluginAlt::$restriction = null;
        DummyConstrainedPlugin::$restriction = null;
    }

    public function testGetName(): void
    {
        $io = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo = $this->createRepositoryMock();

        $runner = new class ($io, $config, $repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };

        $this->assertSame('pre-commit', $runner->getName());
    }

    public function testShouldSkipActionsIsFalseByDefault(): void
    {
        $io = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo = $this->createRepositoryMock();

        $runner = new class ($io, $config, $repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };

        $this->assertFalse($runner->shouldSkipActions());
    }

    public function testShouldSkipActionsCanBeSetToTrue(): void
    {
        $io = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo = $this->createRepositoryMock();

        $runner = new class ($io, $config, $repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };

        $this->assertTrue($runner->shouldSkipActions(true));
        $this->assertTrue($runner->shouldSkipActions());
    }

    public function testShouldSkipActionsCanBeSetToFalse(): void
    {
        $io = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo = $this->createRepositoryMock();

        $runner = new class ($io, $config, $repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };

        $runner->shouldSkipActions(true);

        $this->assertFalse($runner->shouldSkipActions(false));
        $this->assertFalse($runner->shouldSkipActions());
    }

    public function testActionNotApplicable(): void
    {
        $config = $this->createConfigMock();
        $config->method('failOnFirstError')->willReturn(true);
        $dummy = new DummyRepo(['hooks' => ['pre-commit' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $io           = new TestIO();
        $hookConfig   = $this->createHookConfigMock();
        $actionConfig = $this->createActionConfigMock();
        $actionConfig->method('getAction')->willReturn("\\CaptainHook\\App\\Hook\\Message\\Action\\Beams");
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())->method('getActions')->willReturn([$actionConfig]);
        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);

        $runner = new class ($io, $config, $repo) extends Hook {
            protected string $hook = Hooks::PRE_PUSH;
        };
        $runner->run();

        $this->assertStringContainsString('skipped', implode($io->getLog()));
    }

    public function testRunHookWithPlugins(): void
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('not tested on windows');
        }

        $restriction1 = new Restriction(Hooks::POST_CHECKOUT);
        DummyConstrainedPlugin::$restriction = $restriction1;
        DummyConstrainedHookPluginAlt::$restriction = $restriction1;

        $restriction2 = new Restriction(Hooks::PRE_COMMIT);
        DummyConstrainedHookPlugin::$restriction = $restriction2;

        $pluginConfig1 = new Config\Plugin(DummyPlugin::class);
        $pluginConfig2 = new Config\Plugin(DummyHookPlugin::class);
        $pluginConfig3 = new Config\Plugin(DummyConstrainedPlugin::class);
        $pluginConfig4 = new Config\Plugin(DummyConstrainedHookPlugin::class);
        $pluginConfig5 = new Config\Plugin(DummyConstrainedHookPluginAlt::class);

        $config = $this->createConfigMock();
        $config->method('failOnFirstError')->willReturn(true);
        $config->method('getPlugins')->willReturn([
            $pluginConfig1,
            $pluginConfig2,
            $pluginConfig3,
            $pluginConfig4,
            $pluginConfig5,
        ]);
        $dummy = new DummyRepo(['hooks' => ['pre-commit' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $io           = $this->createIOMock();
        $hookConfig   = $this->createHookConfigMock();
        $actionConfig = $this->createActionConfigMock();
        $actionConfig->method('getAction')->willReturn(CH_PATH_FILES . '/bin/success');
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())->method('getActions')->willReturn([$actionConfig, $actionConfig]);
        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $io->expects($this->atLeast(1))->method('write');

        $runner = new class ($io, $config, $repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };
        $runner->run();

        $this->assertSame(2, DummyHookPlugin::$beforeHookCalled);
        $this->assertSame(4, DummyHookPlugin::$beforeActionCalled);
        $this->assertSame(4, DummyHookPlugin::$afterActionCalled);
        $this->assertSame(2, DummyHookPlugin::$afterHookCalled);
    }

    public function testRunHookSkipsActionsFromPluginBeforeHook(): void
    {
        DummyHookPluginSkipsActions::$skipStartAt = 1;

        $pluginConfig = new Config\Plugin(DummyHookPluginSkipsActions::class);

        $dummy = new DummyRepo(['hooks' => ['pre-commit' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $config = $this->createConfigMock();
        $config->method('failOnFirstError')->willReturn(true);
        $config->method('getPlugins')->willReturn([$pluginConfig]);

        $io = $this->createIOMock();
        $hookConfig = $this->createHookConfigMock();
        $actionConfig = $this->createActionConfigMock();
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())->method('getActions')->willReturn([
            $actionConfig,
            $actionConfig,
        ]);
        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $io->expects($this->atLeast(1))->method('write');

        $runner = new class ($io, $config, $repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };
        $runner->run();

        $this->assertSame(1, DummyHookPlugin::$beforeHookCalled);
        $this->assertSame(0, DummyHookPlugin::$beforeActionCalled);
        $this->assertSame(0, DummyHookPlugin::$afterActionCalled);
        $this->assertSame(1, DummyHookPlugin::$afterHookCalled);
    }

    public function testRunHookSkipsActionsFromPluginBeforeAction(): void
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('not tested on windows');
        }

        DummyHookPluginSkipsActions::$skipStartIn = 'beforeAction';
        DummyHookPluginSkipsActions::$skipStartAt = 3;

        $pluginConfig = new Config\Plugin(DummyHookPluginSkipsActions::class);

        $dummy = new DummyRepo(['hooks' => ['pre-commit' => '# hook script']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $config = $this->createConfigMock();
        $config->method('failOnFirstError')->willReturn(true);
        $config->method('getPlugins')->willReturn([$pluginConfig]);

        $io = $this->createIOMock();
        $hookConfig = $this->createHookConfigMock();
        $actionConfig = $this->createActionConfigMock();
        $actionConfig->method('getAction')->willReturn(CH_PATH_FILES . '/bin/success');
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())->method('getActions')->willReturn([
            $actionConfig,
            $actionConfig,
            $actionConfig,
            $actionConfig,
            $actionConfig,
        ]);
        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);
        $config->expects($this->atLeastOnce())->method('isHookEnabled')->willReturn(true);
        $io->expects($this->atLeast(1))->method('write');

        $runner = new class ($io, $config, $repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };
        $runner->run();

        $this->assertSame(1, DummyHookPlugin::$beforeHookCalled);
        $this->assertSame(3, DummyHookPlugin::$beforeActionCalled);
        $this->assertSame(3, DummyHookPlugin::$afterActionCalled);
        $this->assertSame(1, DummyHookPlugin::$afterHookCalled);
    }

    public function testRunHookWhenPluginsAreDisabled(): void
    {
        $pluginConfig1 = new Config\Plugin(DummyHookPlugin::class);
        $pluginConfig2 = new Config\Plugin(DummyHookPlugin::class);

        $config = $this->createConfigMock();
        $config->method('failOnFirstError')->willReturn(false);
        $config->method('getPlugins')->willReturn([$pluginConfig1, $pluginConfig2]);

        $io           = $this->createIOMock();
        $repo         = $this->createRepositoryMock();
        $hookConfig   = $this->createHookConfigMock();
        $actionConfig = $this->createActionConfigMock();
        $actionConfig->expects($this->atLeastOnce())->method('getAction')->willReturn(CH_PATH_FILES . '/bin/success');
        $hookConfig->method('isEnabled')->willReturn(true);
        $hookConfig->expects($this->once())->method('getActions')->willReturn([$actionConfig, clone $actionConfig]);
        $config->method('isHookEnabled')->willReturn(true);
        $config->expects($this->once())->method('getHookConfigToExecute')->willReturn($hookConfig);

        $runner = new class ($io, $config, $repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };
        $runner->setPluginsDisabled(true);
        $runner->run();

        $this->assertSame(0, DummyHookPlugin::$beforeHookCalled);
        $this->assertSame(0, DummyHookPlugin::$beforeActionCalled);
        $this->assertSame(0, DummyHookPlugin::$afterActionCalled);
        $this->assertSame(0, DummyHookPlugin::$afterHookCalled);
    }
}
