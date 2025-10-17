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
 *  Class ChangedFilesTest
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.14.0
 */
class ChangedFilesTest extends TestCase
{
    use IOMockery;
    use AppMockery;
    use ConfigMockery;

    public function testCustomSeparator(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitDiffOperator(['file1.php', 'file2.php', 'README.md']);
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($index);

        $placeholder = new ChangedFiles($io, $config, $repo);
        $command     = $placeholder->replacement(['separated-by' => ', ']);

        $this->assertEquals('file1.php, file2.php, README.md', $command);
    }

    public function testOfType(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitDiffOperator(['file1.php', 'file2.php', 'README.md']);
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($index);

        $placeholder = new ChangedFiles($io, $config, $repo);
        $command     = $placeholder->replacement(['of-type' => 'php']);

        $this->assertEquals('file1.php file2.php', $command);
    }

    public function testFilterByDirectory(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitDiffOperator(['foo/file1.php', 'foo/file2.php', 'README.md']);
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($index);

        $placeholder = new ChangedFiles($io, $config, $repo);
        $command     = $placeholder->replacement(['in-dir' => 'foo/']);

        $this->assertEquals('foo/file1.php foo/file2.php', $command);
    }

    public function testReplaceWith(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitDiffOperator(['foo/file1.php', 'foo/file2.php', 'README.md']);
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($index);

        $placeholder = new ChangedFiles($io, $config, $repo);
        $command     = $placeholder->replacement(['replace' => 'foo/', 'with' => 'bar/']);

        $this->assertEquals('bar/file1.php bar/file2.php README.md', $command);
    }
}
