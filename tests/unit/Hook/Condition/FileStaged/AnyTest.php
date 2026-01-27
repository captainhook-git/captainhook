<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\FileStaged;

use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as CHMockery;
use PHPUnit\Framework\TestCase;

class AnyTest extends TestCase
{
    use IOMockery;
    use CHMockery;

    public function testIsTrue(): void
    {
        $io = $this->createIOMock();
        $operator   = $this->createGitIndexOperator(['fiz.php', 'baz.php', 'foo.php']);
        $repository = $this->createRepositoryMock();
        $repository->expects($this->once())->method('getIndexOperator')->willReturn($operator);

        $fileStaged = new Any(['foo.php', 'bar.php']);

        $this->assertTrue($fileStaged->isTrue($io, $repository));
    }

    public function testIsTrueWithStringFilter(): void
    {
        $io = $this->createIOMock();
        $operator   = $this->createGitIndexOperator(['fiz.php', 'baz.php', 'foo.php']);
        $repository = $this->createRepositoryMock();
        $repository->expects($this->once())->method('getIndexOperator')->willReturn($operator);

        $fileStaged = new Any(['foo.php', 'bar.php'], 'ARC');

        $this->assertTrue($fileStaged->isTrue($io, $repository));
    }

    public function testWithWildcardIsTrue(): void
    {
        $io = $this->createIOMock();
        $operator   = $this->createGitIndexOperator(['fiz.php', 'baz.php', 'foo.php']);
        $repository = $this->createRepositoryMock();
        $repository->expects($this->once())->method('getIndexOperator')->willReturn($operator);

        $fileStaged = new Any(['foo.*', 'bar.php']);

        $this->assertTrue($fileStaged->isTrue($io, $repository));
    }

    public function testIsFalse(): void
    {
        $io = $this->createIOMock();
        $operator   = $this->createGitIndexOperator(['fiz.php', 'baz.php']);
        $repository = $this->createRepositoryMock();
        $repository->expects($this->once())->method('getIndexOperator')->willReturn($operator);

        $fileStaged = new Any(['foo.php', 'bar.php']);

        $this->assertFalse($fileStaged->isTrue($io, $repository));
    }

    public function testWithWildcardIsFalse(): void
    {
        $io = $this->createIOMock();
        $operator   = $this->createGitIndexOperator(['fiz.php', 'baz.php']);
        $repository = $this->createRepositoryMock();
        $repository->expects($this->once())->method('getIndexOperator')->willReturn($operator);

        $fileStaged = new Any(['fo?.php', 'bar.*']);

        $this->assertFalse($fileStaged->isTrue($io, $repository));
    }
}
