<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Template;

use CaptainHook\App\Config\Run;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use CaptainHook\App\Config\Mockery as ConfigMockery;

class DockerTest extends TestCase
{
    use ConfigMockery;

    public function testTemplateCaptainHookDevelopment(): void
    {
        $repo       = realpath(CH_PATH_FILES . '/template-ch');
        $config     = $repo . '/captainhook.json';
        $executable = $repo . '/does/not/matter';
        $pathInfo   = new PathInfo($repo, $config, $executable, false);

        $configMock = $this->createConfigMock(false, $repo . '/captainhook.json');
        $runConfig  = new Run(['mode' => 'docker', 'exec' => 'docker exec cap-container', 'path' => '']);
        $configMock->method('getBootstrap')->willReturn('vendor/autoload.php');
        $configMock->method('getRunConfig')->willReturn($runConfig);

        $template   = new Docker($pathInfo, $configMock);
        $code       = $template->getCode('commit-msg');

        $this->assertStringContainsString('#!/bin/sh', $code);
        $this->assertStringContainsString('docker exec -i cap-container', $code);
        $this->assertStringContainsString('./bin/captainhook', $code);
    }

    public function testTemplateCaptainHookAsLibrary(): void
    {
        $pathInfo = $this->createMock(PathInfo::class);
        $pathInfo->method('getExecutablePath')->willReturn('./vendor/bin/captainhook');
        $pathInfo->method('getConfigPath')->willReturn('captainhook.json');

        $configMock = $this->createConfigMock(false, 'captainhook.json');
        $runConfig  = new Run(['mode' => 'docker', 'exec' => 'docker exec cap-container', 'path' => '']);
        $configMock->method('getBootstrap')->willReturn('');
        $configMock->method('getRunConfig')->willReturn($runConfig);

        $template = new Docker($pathInfo, $configMock);
        $code     = $template->getCode('commit-msg');

        $this->assertStringContainsString('#!/bin/sh', $code);
        $this->assertStringContainsString('docker exec -i cap-container', $code);
        $this->assertStringContainsString('./vendor/bin/captainhook', $code);
    }

    public function testTemplateCustomPath(): void
    {
        $repo       = realpath(CH_PATH_FILES . '/template-ch');
        $executable = $repo . '/does/not/matter';
        $config     = $repo . '/captainhook.json';
        $pathInfo   = new PathInfo($repo, $config, $executable, false);

        $configMock = $this->createConfigMock(false, $repo . '/captainhook.json');
        $runConfig  = new Run(
            ['mode' => 'docker', 'exec' => 'docker exec cap-container', 'path' => './foo/captainhook']
        );
        $configMock->method('getBootstrap')->willReturn('vendor/autoload.php');
        $configMock->method('getRunConfig')->willReturn($runConfig);

        $template = new Docker($pathInfo, $configMock);
        $code     = $template->getCode('commit-msg');

        $this->assertStringContainsString('#!/bin/sh', $code);
        $this->assertStringContainsString('docker exec -i cap-container', $code);
        $this->assertStringContainsString('./foo/captainhook', $code);
        $this->assertStringContainsString('bootstrap=vendor/autoload.php', $code);
    }

    #[DataProvider('replacementPossibilitiesWithoutGitMapping')]
    public function testDockerCommandOptimizationWithoutGitMapping(string $exec, string $expected, string $msg): void
    {
        $pathInfo = $this->createMock(PathInfo::class);
        $pathInfo->method('getExecutablePath')->willReturn('./vendor/bin/captainhook');
        $pathInfo->method('getConfigPath')->willReturn('captainhook.json');

        $configMock = $this->createConfigMock(false, 'captainhook.json');
        $runConfig  = new Run([
            'mode' => 'docker',
            'exec' => 'docker exec ' . $exec,
            'path' => '/usr/local/bin/captainhook'
        ]);
        $configMock->method('getBootstrap')->willReturn('');
        $configMock->method('getRunConfig')->willReturn($runConfig);

        $template = new Docker($pathInfo, $configMock);
        $code     = $template->getCode('prepare-commit-msg');

        $this->assertStringContainsString('docker exec ' . $expected, $code, $msg);
        $this->assertStringContainsString('/usr/local/bin/captainhook', $code);
    }

    public static function replacementPossibilitiesWithoutGitMapping(): array
    {
        return [
            ['cap-container', '-i cap-container', 'none'],
            ['-it cap-container', '-it cap-container', '-it'],
            ['-ti cap-container', '-ti cap-container', '-ti'],
            ['--interactive --tty cap-container', '--interactive --tty cap-container', 'long it'],
            ['--tty --interactive cap-container', '--tty --interactive cap-container', 'long ti'],
            ['--tty cap-container', '-i --tty cap-container', 'no i'],
            ['-xit cap-container', '-xit cap-container', 'prefixed i'],
            ['-xit cap-container', '-xit cap-container', 'prefixed e'],
            ['cap-container', '-i cap-container', 'long e'],
        ];
    }

    #[DataProvider('replacementPossibilitiesWithGitMapping')]
    public function testDockerCommandOptimizationWithGitMapping(string $exec, string $expected, string $msg): void
    {
        $pathInfo = $this->createMock(PathInfo::class);
        $pathInfo->method('getExecutablePath')->willReturn('./vendor/bin/captainhook');
        $pathInfo->method('getConfigPath')->willReturn('captainhook.json');

        $configMock = $this->createConfigMock(false, 'captainhook.json');
        $runConfig  = new Run([
            'mode' => 'docker',
            'exec' => 'docker exec ' . $exec,
            'path' => '/usr/local/bin/captainhook',
            'git'  => '/docker/.git'
        ]);
        $configMock->method('getBootstrap')->willReturn('');
        $configMock->method('getRunConfig')->willReturn($runConfig);

        $template = new Docker($pathInfo, $configMock);
        $code     = $template->getCode('prepare-commit-msg');
        $codePush = $template->getCode('pre-push');

        $this->assertStringContainsString('docker exec' . $expected, $code, $msg);
        $this->assertStringContainsString('/usr/local/bin/captainhook', $code);
        $this->assertStringNotContainsString('docker exec' . $expected, $codePush, $msg);
    }

    public static function replacementPossibilitiesWithGitMapping(): array
    {
        $env = ' -e GIT_INDEX_FILE="/docker/.git/$(basename $GIT_INDEX_FILE)"';
        return [
            ['cap-container', ' -i' . $env . ' cap-container', 'none'],
            ['-it cap-container', $env . ' -it cap-container', '-it'],
            ['-ti cap-container', $env . ' -ti cap-container', '-ti'],
            ['--interactive --tty cap-container', $env . ' --interactive --tty cap-container', 'long it'],
            ['--tty --interactive cap-container', $env . ' --tty --interactive cap-container', 'long ti'],
            ['--tty cap-container', ' -i' . $env . ' --tty cap-container', 'no i'],
            ['-xit cap-container', $env . ' -xit cap-container', 'prefixed i'],
            ['-xit cap-container', $env . ' -xit cap-container', 'prefixed e'],
            ['cap-container', ' -i' . $env . ' cap-container', 'long e'],
        ];
    }
}
