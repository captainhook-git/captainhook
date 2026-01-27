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

use CaptainHook\App\Config as CHConfig;
use PHPUnit\Framework\MockObject\MockBuilder;

trait Mockery
{
    /**
     * Create config mock
     *
     * @param  bool   $loadedFromFile
     * @param  string $path
     * @return \CaptainHook\App\Config&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createConfigMock(bool $loadedFromFile = false, string $path = ''): CHConfig
    {
        $config = $this->getMockBuilder(CHConfig::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $config->method('isLoadedFromFile')->willReturn($loadedFromFile);
        $config->method('getPath')->willReturn($path);

        return $config;
    }

    /**
     * Create hook configuration mock
     *
     * @return \CaptainHook\App\Config\Hook&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createHookConfigMock()
    {
        return $this->getMockBuilder(Hook::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * Create Action configuration mock
     *
     * @return \CaptainHook\App\Config\Action&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createActionConfigMock()
    {
        return $this->getMockBuilder(Action::class)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * Create Action configuration mock
     *
     * @return \CaptainHook\App\Config\Plugin&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createPluginConfigMock(): Plugin
    {
        return $this->getMockBuilder(Plugin::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param  string $type
     * @return \PHPUnit\Framework\MockObject\MockBuilder
     */
    abstract public function getMockBuilder(string $type): MockBuilder;
}
