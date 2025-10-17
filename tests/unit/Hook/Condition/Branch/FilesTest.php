<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\Branch;

use PHPUnit\Framework\TestCase;
use CaptainHook\App\Mockery as AppMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;

class FilesTest extends TestCase
{
    use AppMockery;
    use IOMockery;

    public function testBranchFilesWithReflog(): void
    {
        $io   = $this->createIOMock();
        $repo = $this->createRepositoryMock();
        $info = $this->createGitInfoOperator();
        $log  = $this->createGitLogOperator();

        $log->expects($this->once())
            ->method('getChangedFilesSince')
            ->willReturn(['file1.php', 'file2.php', 'README.md']);
        $log->expects($this->once())
            ->method('getBranchRevFromRefLog')
            ->willReturn('main');
        $info->expects($this->once())
            ->method('getCurrentBranch')
            ->willReturn('foo');

        $repo->expects($this->once())->method('getInfoOperator')->willReturn($info);
        $repo->expects($this->atLeastOnce())->method('getLogOperator')->willReturn($log);

        $files = new Files(['of-type' => 'php']);
        $this->assertTrue($files->isTrue($io, $repo));
    }

    public function testBranchFilesWithEmptyReflog(): void
    {
        $io   = $this->createIOMock();
        $repo = $this->createRepositoryMock();
        $info = $this->createGitInfoOperator();
        $log  = $this->createGitLogOperator();

        $log->expects($this->once())
            ->method('getBranchRevFromRefLog')
            ->willReturn('');
        $info->expects($this->once())
            ->method('getCurrentBranch')
            ->willReturn('foo');

        $repo->expects($this->once())->method('getInfoOperator')->willReturn($info);
        $repo->expects($this->atLeastOnce())->method('getLogOperator')->willReturn($log);

        $files = new Files(['of-type' => 'php']);
        $this->assertFalse($files->isTrue($io, $repo));
    }

    public function testComparedToInDirectory(): void
    {
        $io   = $this->createIOMock();
        $repo = $this->createRepositoryMock();
        $info = $this->createGitInfoOperator();
        $log  = $this->createGitLogOperator();

        $log->expects($this->once())
            ->method('getChangedFilesSince')
            ->willReturn(['foo/file1.php', 'bar/file2.php', 'README.md']);
        $info->expects($this->once())
            ->method('getCurrentBranch')
            ->willReturn('foo');

        $repo->expects($this->once())->method('getInfoOperator')->willReturn($info);
        $repo->expects($this->once())->method('getLogOperator')->willReturn($log);

        $files = new Files(['compared-to' => 'main', 'in-dir' => 'foo/']);
        $this->assertTrue($files->isTrue($io, $repo));
    }
}
