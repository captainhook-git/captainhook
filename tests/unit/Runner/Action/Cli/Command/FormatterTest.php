<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Action\Cli\Command;

use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Mockery as AppMockery;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{
    use IOMockery;
    use AppMockery;
    use ConfigMockery;

    public function testFormatArgumentPlaceholders(): void
    {
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $io     = $this->createIOMock();
        $io->method('getArgument')->with('message-file')->willReturn('bar');

        $formatter = new Formatter($io, $config, $repo);
        $command   = $formatter->format('cmd argument {$FILE}');

        $this->assertEquals('cmd argument bar', $command);
    }

    public function testFormatInvalidPlaceholderReplacedWithEmptyString(): void
    {
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $io     = $this->createIOMock();
        $io->method('getArguments')->willReturn([]);

        $formatter = new Formatter($io, $config, $repo);
        $command   = $formatter->format('cmd argument {$FOO}');

        $this->assertEquals('cmd argument ', $command);
    }

    public function testCachedPlaceholder(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitIndexOperator(['foo/file1.php', 'bar/file2.php', 'baz/file3.php']);
        $io->method('getArguments')->willReturn([]);
        $config->method('getGitDirectory')->willReturn('./');
        $repo->expects($this->exactly(3))->method('getIndexOperator')->willReturn($index);

        $formatter = new Formatter($io, $config, $repo);
        $command1  = $formatter->format('cmd1 argument {$STAGED_FILES|in-dir:foo} {$STAGED_FILES|in-dir:baz}');
        $command2  = $formatter->format('cmd2 argument {$STAGED_FILES}');
        $command3  = $formatter->format('cmd3 argument {$STAGED_FILES}');

        $this->assertEquals('cmd1 argument foo/file1.php baz/file3.php', $command1);
        $this->assertEquals('cmd2 argument foo/file1.php bar/file2.php baz/file3.php', $command2);
        $this->assertEquals('cmd3 argument foo/file1.php bar/file2.php baz/file3.php', $command3);
    }

    public function testComplexPlaceholder(): void
    {
        $io     = $this->createIOMock();
        $config = $this->createConfigMock();
        $repo   = $this->createRepositoryMock();
        $index  = $this->createGitIndexOperator(['file1.php', 'file2.php', 'README.md']);
        $io->method('getArguments')->willReturn([]);
        $repo->expects($this->exactly(2))->method('getIndexOperator')->willReturn($index);
        $index->expects($this->exactly(2))->method('getStagedFilesOfType')->willReturn(['file1.php', 'file2.php']);

        $formatter = new Formatter($io, $config, $repo);
        $command1  = $formatter->format('cmd1 argument {$STAGED_FILES|of-type:php|separated-by:,}');
        $command2  = $formatter->format('cmd2 argument {$STAGED_FILES|of-type:php}');

        $this->assertEquals('cmd1 argument file1.php,file2.php', $command1);
        $this->assertEquals('cmd2 argument file1.php file2.php', $command2);
    }
}
