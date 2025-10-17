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

class IsEmptyTest extends TestCase
{
    use Mockery;

    public function testInvalidConfiguration(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing option "files" for IsEmpty action');

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(IsEmpty::class);

        $repo = $this->createRepositoryMock();
        $repo->method('getIndexOperator')->willReturn($this->createGitIndexOperator(['foo.txt']));

        $standard = new IsEmpty();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testCommitEmptyFile(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(IsEmpty::class, ['files' => [
            CH_PATH_FILES . '/doesNotExist.txt',
            CH_PATH_FILES . '/storage/empty.log',
        ]]);

        $stagedFiles = [CH_PATH_FILES . '/storage/empty.log'];
        $repo        = $this->createRepositoryMock();
        $repo->method('getIndexOperator')->willReturn($this->createGitIndexOperator($stagedFiles));

        $standard = new IsEmpty();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testCommitUnwatchedEmptyFile(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(IsEmpty::class, ['files' => [
            CH_PATH_FILES . '/doesNotExist.txt',
        ]]);

        $stagedFiles = [CH_PATH_FILES . '/empty.log'];
        $repo        = $this->createRepositoryMock();
        $repo->method('getIndexOperator')->willReturn($this->createGitIndexOperator($stagedFiles));

        $standard = new IsEmpty();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testFailCommitFileWithContents(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(IsEmpty::class, ['files' => [
            CH_PATH_FILES . '/doesNotExist.txt',  // pass
            CH_PATH_FILES . '/storage/empty.log', // pass
            CH_PATH_FILES . '/storage/test.json', // fail
        ]]);

        $stagedFiles = [CH_PATH_FILES . '/storage/empty.log', CH_PATH_FILES . '/storage/test.json'];
        $repo        = $this->createRepositoryMock();
        $repo->method('getIndexOperator')->willReturn($this->createGitIndexOperator($stagedFiles));

        $standard = new IsEmpty();
        $standard->execute($config, $io, $repo, $action);
    }
}
