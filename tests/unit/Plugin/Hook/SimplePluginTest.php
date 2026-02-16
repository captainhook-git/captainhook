<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Plugin\Hook;

use CaptainHook\App\Config\Action;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Config\Plugin;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as CHMockery;
use CaptainHook\App\Runner\Hook;
use PHPUnit\Framework\TestCase;

class SimplePluginTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testBeforeHook(): void
    {
        $hook   = $this->createMock(Hook::class);
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $io->expects($this->once())->method('write');

        $plugin = new SimplePlugin();
        $plugin->configure($config, $io, $repo, new Plugin(SimplePlugin::class));

        $plugin->beforeHook($hook);
    }

    public function testAfterHook(): void
    {
        $hook   = $this->createMock(Hook::class);
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $io->expects($this->once())->method('write');

        $plugin = new SimplePlugin();
        $plugin->configure($config, $io, $repo, new Plugin(SimplePlugin::class));

        $plugin->afterHook($hook);
    }

    public function testBeforeAction(): void
    {
        $hook   = $this->createMock(Hook::class);
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $io->expects($this->once())->method('write');

        $plugin = new SimplePlugin();
        $plugin->configure($config, $io, $repo, new Plugin(SimplePlugin::class));

        $plugin->beforeAction($hook, new Action('foo'));
    }

    public function testAfterAction(): void
    {
        $hook   = $this->createMock(Hook::class);
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $io->expects($this->once())->method('write');

        $plugin = new SimplePlugin();
        $plugin->configure($config, $io, $repo, new Plugin(SimplePlugin::class));

        $plugin->afterAction($hook, new Action('foo'));
    }
}
