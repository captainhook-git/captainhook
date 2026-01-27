<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\FileChanged;

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
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator   = $this->createGitDiffOperator(['fiz.php', 'baz.php', 'foo.php']);
        $repository = $this->createRepositoryMock();
        $repository->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $fileChange = new Any(['foo.php', 'bar.php']);

        $this->assertTrue($fileChange->isTrue($io, $repository));
    }

    public function testIsTrueAfterRewrite(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->atLeastOnce())->method('getArgument')->willReturn('hook:post-rewrite');
        $io->expects($this->atLeastOnce())->method('getStandardInput')->willReturn(['firstHash secondHash']);
        $operator   = $this->createGitDiffOperator(['fiz.php', 'baz.php', 'foo.php']);
        $repository = $this->createRepositoryMock();
        $repository->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $fileChange = new Any(['foo.php', 'bar.php']);

        $this->assertTrue($fileChange->isTrue($io, $repository));
    }

    public function testWithWildcardIsTrue(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator   = $this->createGitDiffOperator(['fiz.php', 'baz.php', 'foo.php']);
        $repository = $this->createRepositoryMock();
        $repository->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $fileChange = new Any(['foo.*', 'bar.php']);

        $this->assertTrue($fileChange->isTrue($io, $repository));
    }

    public function testIsFalse(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator   = $this->createGitDiffOperator(['fiz.php', 'baz.php']);
        $repository = $this->createRepositoryMock();
        $repository->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $fileChange = new Any(['foo.php', 'bar.php']);

        $this->assertFalse($fileChange->isTrue($io, $repository));
    }

    public function testWithWildcardIsFalse(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator   = $this->createGitDiffOperator(['fiz.php', 'baz.php']);
        $repository = $this->createRepositoryMock();
        $repository->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $fileChange = new Any(['fo?.php', 'bar.*']);

        $this->assertFalse($fileChange->isTrue($io, $repository));
    }
}
