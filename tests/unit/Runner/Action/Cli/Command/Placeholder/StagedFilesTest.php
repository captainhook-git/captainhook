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

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Mockery as AppMockery;
use PHPUnit\Framework\TestCase;

class StagedFilesTest extends TestCase
{
    use AppMockery;
    use ConfigMockery;

    /**
     * Tests StagedFiles::replacement
     */
    public function testCustomSeparator(): void
    {
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitIndexOperator(['file1.php', 'file2.php', 'README.md']);
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($index);

        $placeholder = new StagedFiles($config, $repo);
        $command     = $placeholder->replacement(['separated-by' => ', ']);

        $this->assertEquals('file1.php, file2.php, README.md', $command);
    }

    /**
     * Tests StagedFiles::replacement
     */
    public function testCustomDiffFilter(): void
    {
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitIndexOperator(['file1.php', 'file2.php', 'README.md']);
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($index);

        $placeholder = new StagedFiles($config, $repo);
        $command     = $placeholder->replacement(['diff-filter' => 'AM']);

        $this->assertEquals('file1.php file2.php README.md', $command);
    }

    /**
     * Tests StagedFiles::replacement
     */
    public function testOfType(): void
    {
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitIndexOperator(['file1.php', 'file2.php', 'README.md']);
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($index);
        $index->expects($this->once())->method('getStagedFilesOfType')->willReturn(['file1.php', 'file2.php']);

        $placeholder = new StagedFiles($config, $repo);
        $command     = $placeholder->replacement(['of-type' => 'php']);

        $this->assertEquals('file1.php file2.php', $command);
    }

    /**
     * Tests StagedFiles::replacement
     */
    public function testFilterByDirectory(): void
    {
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitIndexOperator(['foo/file1.php', 'foo/file2.php', 'README.md']);
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($index);

        $placeholder = new StagedFiles($config, $repo);
        $command     = $placeholder->replacement(['in-dir' => 'foo/']);

        $this->assertEquals('foo/file1.php foo/file2.php', $command);
    }

    /**
     * Tests StagedFiles::replacement
     */
    public function testReplaceWith(): void
    {
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitIndexOperator(['foo/file1.php', 'foo/file2.php', 'README.md']);
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($index);

        $placeholder = new StagedFiles($config, $repo);
        $command     = $placeholder->replacement(['replace' => 'foo/', 'with' => 'bar/']);

        $this->assertEquals('bar/file1.php bar/file2.php README.md', $command);
    }
}
