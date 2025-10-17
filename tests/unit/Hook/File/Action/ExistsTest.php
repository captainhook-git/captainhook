<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\File\Action;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Mockery;
use Exception;
use PHPUnit\Framework\TestCase;

class ExistsTest extends TestCase
{
    use Mockery;

    public function testInvalidConfiguration(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Exists::class);

        $repo = $this->createRepositoryMock();
        $info = $this->createGitInfoOperator();
        $repo->method('getInfoOperator')->willReturn($info);
        $info->method('getFilesInTree')->willReturn([]);

        $exists = new Exists();
        $exists->execute($config, $io, $repo, $action);
    }

    public function testFileExists(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(IsEmpty::class, ['files' => [
            'tests/**/*Tests.php'
        ]]);

        $repoFiles = ['tests/Demo/DemoTest.php'];

        $repo = $this->createRepositoryMock();
        $info = $this->createGitInfoOperator();
        $repo->method('getInfoOperator')->willReturn($info);
        $info->method('getFilesInTree')->willReturn($repoFiles);

        $exists = new Exists();
        $exists->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testFileDoesNotExist(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(IsEmpty::class, ['files' => [
            'tests/**/*Tests.php'
        ]]);

        $repo = $this->createRepositoryMock();
        $info = $this->createGitInfoOperator();
        $repo->method('getInfoOperator')->willReturn($info);
        $info->method('getFilesInTree')->willReturn([]);

        $exists = new Exists();
        $exists->execute($config, $io, $repo, $action);
    }
}
