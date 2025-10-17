<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Git\ChangedFiles\Detector;

use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as AppMockery;
use PHPUnit\Framework\TestCase;

/**
 * Class PrePushTest
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.20.0
 */
class PrePushTest extends TestCase
{
    use IOMockery;
    use AppMockery;

    public function testGetChangedFilesNoRanges(): void
    {
        $io    = $this->createIOMock();
        $repo  = $this->createRepositoryMock();

        $io->expects($this->atLeastOnce())
            ->method('getStandardInput')
            ->willReturn([]);

        $d     = new PrePush($io, $repo);
        $files = $d->getChangedFiles();

        $this->assertEquals([], $files);
    }

    public function testGetChangedFilesRemoteExists(): void
    {
        $io    = $this->createIOMock();
        $repo  = $this->createRepositoryMock();
        $diff  = $this->createGitDiffOperator(['foo.txt', 'bar.txt']);
        $log   = $this->createGitLogOperator();

        $io->expects($this->atLeastOnce())
            ->method('getStandardInput')
            ->willReturn(
                [
                    'refs/heads/main 9dfa0fa6221d75f48b2dfac359127324bedf8409' .
                    ' refs/heads/main 8309f6e16097754469c485e604900c573bf2c5d8'
                ]
            );

        $repo->method('getLogOperator')->willReturn($log);
        $repo->method('getDiffOperator')->willReturn($diff);

        $d     = new PrePush($io, $repo);
        $files = $d->getChangedFiles();

        $this->assertEquals('foo.txt', $files[0]);
    }

    public function testGetChangedFilesNewBranch(): void
    {
        $io    = $this->createIOMock();
        $repo  = $this->createRepositoryMock();
        $diff  = $this->createGitDiffOperator(['foo.txt', 'bar.txt']);
        $log   = $this->createGitLogOperator();

        $io->expects($this->atLeastOnce())
            ->method('getStandardInput')
            ->willReturn(
                [
                    'refs/heads/main 9dfa0fa6221d75f48b2dfac359127324bedf8409' .
                    ' refs/heads/main 0000000000000000000000000000000000000000'
                ]
            );

        $log->expects($this->atLeastOnce())
            ->method('getBranchRevFromRefLog')
            ->willReturn('8309f6e16097754469c485e604900c573bf2c5d8');

        $repo->method('getLogOperator')->willReturn($log);
        $repo->method('getDiffOperator')->willReturn($diff);

        $d = new PrePush($io, $repo);
        $d->useReflogFallback(true);
        $files = $d->getChangedFiles();

        $this->assertEquals('foo.txt', $files[0]);
    }

    public function testGetChangedFilesNewBranchNoReflog(): void
    {
        $io    = $this->createIOMock();
        $repo  = $this->createRepositoryMock();
        $diff  = $this->createGitDiffOperator(['foo.txt', 'bar.txt']);
        $log   = $this->createGitLogOperator();

        $io->expects($this->atLeastOnce())
            ->method('getStandardInput')
            ->willReturn(
                [
                    'refs/heads/main 9dfa0fa6221d75f48b2dfac359127324bedf8409' .
                    ' refs/heads/main 0000000000000000000000000000000000000000'
                ]
            );

        $log->expects($this->atLeastOnce())
            ->method('getBranchRevFromRefLog')
            ->willReturn('');
        $log->expects($this->atLeastOnce())
            ->method('getBranchRevsFromRefLog')
            ->willReturn(['9dfa0fa6221d75f48b2dfac359127324bedf8409', '8309f6e16097754469c485e604900c573bf2c5d8']);
        $log->expects($this->atLeastOnce())
            ->method('getChangedFilesInRevisions')
            ->willReturn(['foo.txt', 'bar.txt']);

        $repo->method('getLogOperator')->willReturn($log);
        $repo->method('getDiffOperator')->willReturn($diff);

        $d = new PrePush($io, $repo);
        $d->useReflogFallback(true);

        $files = $d->getChangedFiles();

        $this->assertEquals('foo.txt', $files[0]);
    }
}
