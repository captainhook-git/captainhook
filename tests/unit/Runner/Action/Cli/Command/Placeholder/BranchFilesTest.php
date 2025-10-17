<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Action\Cli\Command\Placeholder;

use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Mockery as AppMockery;
use PHPUnit\Framework\TestCase;

/**
 *  Class BranchFilesTest
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.14.0
 */
class BranchFilesTest extends TestCase
{
    use IOMockery;
    use AppMockery;
    use ConfigMockery;

    public function testFindStarByReflogWithCustomSeparator(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $info   = $this->createGitInfoOperator();
        $log    = $this->createGitLogOperator();
        $diff   = $this->createGitDiffOperator(['file1.php', 'file2.php', 'README.md']);

        $log->expects($this->once())
            ->method('getBranchRevFromRefLog')
            ->willReturn('main');
        $info->expects($this->once())
             ->method('getCurrentBranch')
             ->willReturn('foo');

        $repo->expects($this->atLeastOnce())->method('getLogOperator')->willReturn($log);
        $repo->expects($this->atLeastOnce())->method('getDiffOperator')->willReturn($diff);
        $repo->expects($this->atLeastOnce())->method('getInfoOperator')->willReturn($info);

        $placeholder = new BranchFiles($io, $config, $repo);
        $replaced    = $placeholder->replacement(['separated-by' => ',']);

        $this->assertEquals('file1.php,file2.php,README.md', $replaced);
    }

    public function testCantFindStartByReflog(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $info   = $this->createGitInfoOperator();
        $log    = $this->createGitLogOperator();

        $log->expects($this->once())
            ->method('getBranchRevFromRefLog')
            ->willReturn('');
        $info->expects($this->once())
            ->method('getCurrentBranch')
            ->willReturn('foo');

        $repo->expects($this->atLeastOnce())->method('getLogOperator')->willReturn($log);
        $repo->expects($this->atLeastOnce())->method('getInfoOperator')->willReturn($info);

        $placeholder = new BranchFiles($io, $config, $repo);
        $replaced    = $placeholder->replacement([]);

        $this->assertEquals('', $replaced);
    }

    public function testCompareToOfType(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $info   = $this->createGitInfoOperator();
        $diff   = $this->createGitDiffOperator(['file1.php', 'file2.php', 'README.md', 'foo.txt']);

        $info->expects($this->once())
             ->method('getCurrentBranch')
             ->willReturn('foo');

        $repo->expects($this->atLeastOnce())->method('getDiffOperator')->willReturn($diff);
        $repo->expects($this->atLeastOnce())->method('getInfoOperator')->willReturn($info);

        $placeholder = new BranchFiles($io, $config, $repo);
        $replaced    = $placeholder->replacement(['compared-to' => 'main', 'of-type' => 'php']);

        $this->assertEquals('file1.php file2.php', $replaced);
    }

    public function testFilterByDirectory(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $info   = $this->createGitInfoOperator();
        $diff   = $this->createGitDiffOperator(['foo/file1.php', 'foo/file2.php', 'README.md', 'foo.txt']);

        $info->expects($this->once())
             ->method('getCurrentBranch')
             ->willReturn('foo');

        $repo->expects($this->atLeastOnce())->method('getDiffOperator')->willReturn($diff);
        $repo->expects($this->atLeastOnce())->method('getInfoOperator')->willReturn($info);

        $placeholder = new BranchFiles($io, $config, $repo);
        $replaced    = $placeholder->replacement(['compared-to' => 'main', 'in-dir' => 'foo/']);

        $this->assertEquals('foo/file1.php foo/file2.php', $replaced);
    }

    public function testReplaceWith(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $info   = $this->createGitInfoOperator();
        $diff   = $this->createGitDiffOperator(['foo/file1.php', 'foo/file2.php', 'README.md']);

        $info->expects($this->once())
            ->method('getCurrentBranch')
            ->willReturn('foo');

        $repo->expects($this->atLeastOnce())->method('getDiffOperator')->willReturn($diff);
        $repo->expects($this->atLeastOnce())->method('getInfoOperator')->willReturn($info);

        $placeholder = new BranchFiles($io, $config, $repo);
        $replaced    = $placeholder->replacement(['compared-to' => 'main', 'replace' => 'foo/', 'with' => 'bar/']);

        $this->assertEquals('bar/file1.php bar/file2.php README.md', $replaced);
    }
}
