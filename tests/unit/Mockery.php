<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App;

use CaptainHook\App\Console\Runtime\Resolver;
use PHPUnit\Framework\MockObject\MockBuilder;
use SebastianFeldmann\Git\Operator\Diff;
use SebastianFeldmann\Git\Operator\Index;
use SebastianFeldmann\Git\Operator\Config;
use SebastianFeldmann\Git\Operator\Info;
use SebastianFeldmann\Git\Operator\Log;
use SebastianFeldmann\Git\Operator\Remote;
use SebastianFeldmann\Git\Repository;

trait Mockery
{
    /**
     * Create resolver mock
     *
     * @param  string $executable
     * @param  bool   $isPhar
     * @return \CaptainHook\App\Console\Runtime\Resolver&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createResolverMock(string $executable = 'vendor/bin/captainhook', bool $isPhar = false): Resolver
    {
        $repo = $this->getMockBuilder(Resolver::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $repo->method('getExecutable')->willReturn($executable);
        $repo->method('isPharRelease')->willReturn($isPhar);

        return $repo;
    }

    /**
     * Create repository mock
     *
     * @param  string $root
     * @return \SebastianFeldmann\Git\Repository&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createRepositoryMock(string $root = '', string $hooksDir = ''): Repository
    {

        $repo = $this->getMockBuilder(Repository::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $repo->method('getRoot')->willReturn($root);
        $repo->method('getHooksDir')->willReturn(empty($hooksDir) ? $root . '/.git/hooks' : $hooksDir);

        return $repo;
    }

    /**
     * Create info operator mock
     *
     * @param  string $tag
     * @param  string $branch
     * @return \SebastianFeldmann\Git\Operator\Info&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createGitInfoOperator(string $tag = 'v1.0.0', string $branch = 'master'): Info
    {
        $operator = $this->getMockBuilder(Info::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $operator->method('getCurrentTag')->willReturn($tag);
        $operator->method('getCurrentBranch')->willReturn($branch);

        return $operator;
    }

    /**
     * Create diff operator mock
     *
     * @param  array $changedFiles
     * @return \SebastianFeldmann\Git\Operator\Diff&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createGitDiffOperator(array $changedFiles = []): Diff
    {
        $operator = $this->getMockBuilder(Diff::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $operator->method('getChangedFiles')->willReturn($changedFiles);

        return $operator;
    }

    /**
     * Create remote operator mock
     *
     * @return \SebastianFeldmann\Git\Operator\Remote&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createGitRemoteOperator(): Remote
    {
        return $this->getMockBuilder(Remote::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Create log operator mock
     *
     * @return \SebastianFeldmann\Git\Operator\Log&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createGitLogOperator(): Log
    {
        $operator = $this->getMockBuilder(Log::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $operator;
    }

    /**
     * Create index operator mock
     *
     * @param  array $stagedFiles
     * @return \SebastianFeldmann\Git\Operator\Index&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createGitIndexOperator(array $stagedFiles = []): Index
    {
        $operator = $this->getMockBuilder(Index::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $operator->method('getStagedFiles')->willReturn($stagedFiles);

        return $operator;
    }

    /**
     * Create config operator mock
     *
     * @return \SebastianFeldmann\Git\Operator\Config&\PHPUnit\Framework\MockObject\MockObject
     */
    public function createGitConfigOperator(): Config
    {
        return $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @param  $type
     * @return \PHPUnit\Framework\MockObject\MockBuilder
     */
    abstract public function getMockBuilder(string $type): MockBuilder;
}
