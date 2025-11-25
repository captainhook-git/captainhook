<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\FileChanged;

use PHPUnit\Framework\TestCase;
use CaptainHook\App\Mockery as AppMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;

class ThatIsTest extends TestCase
{
    use AppMockery;
    use IOMockery;

    public function testPreCommitRestriction(): void
    {
        $this->assertTrue(ThatIs::getRestriction()->isApplicableFor('post-checkout'));
        $this->assertFalse(ThatIs::getRestriction()->isApplicableFor('pre-commit'));
    }

    public function testChangedTrueType(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator = $this->createGitDiffOperator(['foo.php', 'bar.php']);
        $repo     = $this->createRepositoryMock('');
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $thatIs = new ThatIs(['ofType' => 'php']);
        $this->assertTrue($thatIs->isTrue($io, $repo));
    }

    public function testStagedTrueMultipleType(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator = $this->createGitDiffOperator(['foo.php', 'bar.php']);
        $repo     = $this->createRepositoryMock('');
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $thatIs = new ThatIs(['ofType' => ['php', 'js']]);
        $this->assertTrue($thatIs->isTrue($io, $repo));
    }

    public function testStagedFalseMultipleType(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator = $this->createGitDiffOperator(['foo.php', 'bar.php']);
        $repo     = $this->createRepositoryMock('');
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $thatIs = new ThatIs(['ofType' => ['ts', 'js']]);
        $this->assertFalse($thatIs->isTrue($io, $repo));
    }

    public function testStagedTrueDirectory(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator = $this->createGitDiffOperator(['foo/foo.php', 'bar/bar.js', 'fiz/baz.txt']);
        $repo     = $this->createRepositoryMock('');
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $thatIs = new ThatIs(['inDirectory' => 'bar/']);
        $this->assertTrue($thatIs->isTrue($io, $repo));
    }

    public function testStagedFalsePartialDirectory(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator = $this->createGitDiffOperator(['foo/foo.php', 'foo/bar/bar.js', 'fiz/baz.txt']);
        $repo     = $this->createRepositoryMock('');
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $thatIs = new ThatIs(['inDirectory' => 'bar/']);
        $this->assertFalse($thatIs->isTrue($io, $repo));
    }

    public function testStagedTrueMultipleDirectory(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator = $this->createGitDiffOperator(['foo/foo.php', 'foo/bar/bar.js', 'fiz/baz.txt']);
        $repo     = $this->createRepositoryMock('');
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $thatIs = new ThatIs(['inDirectory' => ['foo/bar/', 'baz/']]);
        $this->assertTrue($thatIs->isTrue($io, $repo));
    }

    public function testStagedFalseMultipleDirectory(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator = $this->createGitDiffOperator(['foo/foo.php', 'foo/bar/bar.js', 'fiz/baz.txt']);
        $repo     = $this->createRepositoryMock('');
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $thatIs = new ThatIs(['inDirectory' => ['foobar/', 'baz/'], 'diffFilter' => ['A', 'C']]);
        $this->assertFalse($thatIs->isTrue($io, $repo));
    }

    public function testStagedFalseDirectoryAndType(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator = $this->createGitDiffOperator(['foo/foo.php', 'bar/bar.js']);
        $repo     = $this->createRepositoryMock('');
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $thatIs = new ThatIs(['inDirectory' => 'bar/', 'ofType' => 'php']);
        $this->assertFalse($thatIs->isTrue($io, $repo));
    }

    public function testStagedFalse(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator = $this->createGitDiffOperator(['foo.php']);
        $repo     = $this->createRepositoryMock('');
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $thatIs = new ThatIs(['ofType' => 'js']);
        $this->assertFalse($thatIs->isTrue($io, $repo));
    }
}
