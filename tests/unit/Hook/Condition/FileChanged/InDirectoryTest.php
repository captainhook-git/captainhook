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

class InDirectoryTest extends TestCase
{
    use AppMockery;
    use IOMockery;

    public function testIsProperlyRestricted(): void
    {
        $this->assertTrue(InDirectory::getRestriction()->isApplicableFor('post-checkout'));
        $this->assertFalse(InDirectory::getRestriction()->isApplicableFor('pre-commit'));
    }

    public function testInDirectory(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator   = $this->createGitDiffOperator(['foo/foo.php', 'bar/bar.php', 'bar/baz.php']);
        $repository = $this->createRepositoryMock('');
        $repository->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $fileChange = new InDirectory(['foo']);

        $this->assertTrue($fileChange->isTrue($io, $repository));
    }

    public function testNotInDirectory(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');
        $operator   = $this->createGitDiffOperator(['foo/foo.php', 'bar/bar.php', 'bar/baz.php']);
        $repository = $this->createRepositoryMock('');
        $repository->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $condition = new InDirectory('tests/', ['A', 'C']);
        $this->assertFalse($condition->isTrue($io, $repository));
    }
}
