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
use CaptainHook\App\Mockery as GitMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use Exception;
use PHPUnit\Framework\TestCase;

class IsNotEmptyTest extends TestCase
{
    use GitMockery;
    use IOMockery;

    public function testRestrictionValid(): void
    {
        $restriction = IsNotEmpty::getRestriction();
        $this->assertTrue($restriction->isApplicableFor('pre-commit'));
    }

    public function testRestrictionInvalid(): void
    {
        $restriction = IsNotEmpty::getRestriction();
        $this->assertFalse($restriction->isApplicableFor('pre-push'));
    }

    public function testCommitNotEmptyFile(): void
    {

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(
            IsNotEmpty::class,
            [
                'files' => [
                    CH_PATH_FILES . '/storage/test.json'
                ]
            ]
        );

        $stagedFiles = [CH_PATH_FILES . '/storage/test.json'];
        $repo        = $this->createRepositoryMock();
        $repo->method('getIndexOperator')->willReturn($this->createGitIndexOperator($stagedFiles));

        $isNotEmpty = new IsNotEmpty();
        $isNotEmpty->execute($config, $io, $repo, $action);

        // no error should happen
        $this->assertTrue(true);
    }

    public function testConfigWithGlobs(): void
    {
        $io     = $this->createIOMock();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(
            IsNotEmpty::class,
            [
                'files' => [
                    CH_PATH_FILES . '/storage/*.txt',
                    CH_PATH_FILES . '/storage/*.json',
                ]
            ]
        );

        // with this configuration the Captain should find 4 files for 2 patterns
        $io->expects($this->atLeast(1))->method('write');

        // two of those files should be in the commit
        $stagedFiles = [CH_PATH_FILES . '/storage/regextest1.txt', CH_PATH_FILES . '/storage/test.json'];
        $repo        = $this->createRepositoryMock();
        $repo->method('getIndexOperator')->willReturn($this->createGitIndexOperator($stagedFiles));

        $isNotEmpty = new IsNotEmpty();
        $isNotEmpty->execute($config, $io, $repo, $action);

        // no error should happen
        $this->assertTrue(true);
    }

    public function testFailCommitEmptyFile(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(
            IsNotEmpty::class,
            [
                'files' => [
                    CH_PATH_FILES . '/storage/empty.log',
                ]
            ]
        );

        $stagedFiles = [CH_PATH_FILES . '/storage/empty.log'];
        $repo        = $this->createRepositoryMock();
        $repo->method('getIndexOperator')->willReturn($this->createGitIndexOperator($stagedFiles));

        $isNotEmpty = new IsNotEmpty();
        $isNotEmpty->execute($config, $io, $repo, $action);
    }
}
