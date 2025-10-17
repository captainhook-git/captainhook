<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Template\Local;

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Config\Run;
use CaptainHook\App\Hook\Template\PathInfo;
use PHPUnit\Framework\TestCase;

class WSLTest extends ShellTest
{
    use ConfigMockery;

    public function testTemplate(): void
    {
        $pathInfo = $this->createMock(PathInfo::class);
        $pathInfo->method('getExecutablePath')->willReturn('vendor/bin/captainhook');
        $pathInfo->method('getConfigPath')->willReturn('captainhook.json');
        $pathInfo->method('isPhar')->willReturn(false);

        $config = $this->createConfigMock(false, 'captainhook.json');
        $config->method('getBootstrap')->willReturn('vendor/autoload.php');
        $config->method('getPhpPath')->willReturn('');

        $template = new WSL($pathInfo, $config);
        $code     = $template->getCode('commit-msg');

        $this->assertStringContainsString('#!/bin/sh', $code);
        $this->assertStringContainsString('commit-msg', $code);
        $this->assertStringNotContainsString('php7.4', $code);
        $this->assertStringContainsString('wsl.exe vendor/bin/captainhook $INTERACTIVE', $code);
    }

    public function testTemplateWithDefinedPHP(): void
    {
        $pathInfo = $this->createMock(PathInfo::class);
        $pathInfo->method('getExecutablePath')->willReturn('vendor/bin/captainhook');
        $pathInfo->method('getConfigPath')->willReturn('captainhook.json');
        $pathInfo->method('isPhar')->willReturn(false);

        $config = $this->createConfigMock(false, 'captainhook.json');
        $config->method('getBootstrap')->willReturn('vendor/autoload.php');
        $config->method('getPhpPath')->willReturn('/usr/bin/php7.4');

        $template = new WSL($pathInfo, $config);
        $code     = $template->getCode('commit-msg');

        $this->assertStringContainsString('#!/bin/sh', $code);
        $this->assertStringContainsString('commit-msg', $code);
        $this->assertStringContainsString('/usr/bin/php7.4', $code);
        $this->assertStringContainsString('wsl.exe /usr/bin/php7.4 vendor/bin/captainhook $INTERACTIVE', $code);
    }

    public function testTemplateWithDefinedPHPAndRunPath(): void
    {
        $pathInfo = $this->createMock(PathInfo::class);
        $pathInfo->method('getExecutablePath')->willReturn('vendor/bin/captainhook');
        $pathInfo->method('getConfigPath')->willReturn('captainhook.json');
        $pathInfo->method('isPhar')->willReturn(false);

        $config    = $this->createConfigMock(false, 'captainhook.json');
        $runConfig = new Run(['path' => 'tools/captainhook.phar']);
        $config->method('getRunConfig')->willReturn($runConfig);
        $config->method('getBootstrap')->willReturn('vendor/autoload.php');
        $config->method('getPhpPath')->willReturn('/usr/bin/php7.4');

        $template = new WSL($pathInfo, $config);
        $code     = $template->getCode('commit-msg');

        $this->assertStringContainsString('#!/bin/sh', $code);
        $this->assertStringContainsString('commit-msg', $code);
        $this->assertStringContainsString('/usr/bin/php7.4', $code);
        $this->assertStringContainsString('wsl.exe /usr/bin/php7.4 tools/captainhook.phar $INTERACTIVE', $code);
    }

    public function testTemplateExtExecutable(): void
    {
        $pathInfo = $this->createMock(PathInfo::class);
        $pathInfo->method('getExecutablePath')->willReturn('/usr/local/bin/captainhook');
        $pathInfo->method('getConfigPath')->willReturn('captainhook.json');
        $pathInfo->method('isPhar')->willReturn(false);

        $config = $this->createConfigMock(false, 'captainhook.json');
        $config->method('getBootstrap')->willReturn('vendor/autoload.php');
        $config->method('getPhpPath')->willReturn('');

        $template = new WSL($pathInfo, $config);
        $code     = $template->getCode('commit-msg');

        $this->assertStringContainsString('#!/bin/sh', $code);
        $this->assertStringContainsString('commit-msg', $code);
        $this->assertStringNotContainsString('php7.4', $code);
        $this->assertStringContainsString('wsl.exe /usr/local/bin/captainhook $INTERACTIVE', $code);
    }

    public function testTemplateExtExecutableWithDefinedPHP(): void
    {
        $pathInfo = $this->createMock(PathInfo::class);
        $pathInfo->method('getExecutablePath')->willReturn('/usr/local/bin/captainhook');
        $pathInfo->method('getConfigPath')->willReturn('captainhook.json');
        $pathInfo->method('isPhar')->willReturn(false);

        $config = $this->createConfigMock(false, 'captainhook.json');
        $config->method('getBootstrap')->willReturn('vendor/autoload.php');
        $config->method('getPhpPath')->willReturn('/usr/bin/php7.4');

        $template = new WSL($pathInfo, $config);
        $code     = $template->getCode('commit-msg');

        $this->assertStringContainsString('#!/bin/sh', $code);
        $this->assertStringContainsString('commit-msg', $code);

        $this->assertStringContainsString('/usr/bin/php7.4', $code);
        $this->assertStringContainsString('wsl.exe /usr/bin/php7.4 /usr/local/bin/captainhook $INTERACTIVE', $code);
    }

    public function testTemplateExtExecutableWithUserInput(): void
    {
        $pathInfo = $this->createMock(PathInfo::class);
        $pathInfo->method('getExecutablePath')->willReturn('/usr/local/bin/captainhook');
        $pathInfo->method('getConfigPath')->willReturn('captainhook.json');
        $pathInfo->method('isPhar')->willReturn(false);

        $config = $this->createConfigMock(false, 'captainhook.json');
        $config->method('getBootstrap')->willReturn('vendor/autoload.php');
        $config->method('getPhpPath')->willReturn('');

        $template = new WSL($pathInfo, $config);
        $code     = $template->getCode('prepare-commit-msg');

        $this->assertStringContainsString('#!/bin/sh', $code);
        $this->assertStringContainsString('commit-msg', $code);
        $this->assertStringContainsString('wsl.exe /usr/local/bin/captainhook $INTERACTIVE', $code);
    }
}
