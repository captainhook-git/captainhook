<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\FileStaged;

use PHPUnit\Framework\TestCase;
use CaptainHook\App\Mockery as AppMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;

class InDirectoryTest extends TestCase
{
    use AppMockery;
    use IOMockery;

    public function testPreCommitRestriction(): void
    {
        $this->assertTrue(InDirectory::getRestriction()->isApplicableFor('pre-commit'));
        $this->assertFalse(InDirectory::getRestriction()->isApplicableFor('pre-push'));
    }

    public function testStagedTrue(): void
    {
        $io    = $this->createIOMock();
        $repo  = $this->createRepositoryMock();
        $index = $this->createGitIndexOperator(['src/foo.php', 'tests/foo.php']);
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($index);

        $condition = new InDirectory('src/');
        $this->assertTrue($condition->isTrue($io, $repo));
    }

    public function testStagedFalse(): void
    {
        $io    = $this->createIOMock();
        $repo  = $this->createRepositoryMock();
        $index = $this->createGitIndexOperator(['src/foo.php', 'src/bar.php']);
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($index);

        $condition = new InDirectory('tests/', ['A', 'C']);
        $this->assertFalse($condition->isTrue($io, $repo));
    }
}
