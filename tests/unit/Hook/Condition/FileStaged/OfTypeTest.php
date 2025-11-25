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

class OfTypeTest extends TestCase
{
    use AppMockery;
    use IOMockery;

    public function testPreCommitRestriction(): void
    {
        $this->assertTrue(OfType::getRestriction()->isApplicableFor('pre-commit'));
        $this->assertFalse(OfType::getRestriction()->isApplicableFor('pre-push'));
    }

    public function testStagedTrue(): void
    {
        $io    = $this->createIOMock();
        $repo  = $this->createRepositoryMock();
        $index = $this->createGitIndexOperator(['foo.php', 'bar.php', 'baz.txt']);
        $index->expects($this->once())->method('getStagedFiles');
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($index);

        $ofType = new OfType('php');
        $this->assertTrue($ofType->isTrue($io, $repo));
    }

    public function testStagedMultipleTrue(): void
    {
        $io    = $this->createIOMock();
        $repo  = $this->createRepositoryMock();
        $index = $this->createGitIndexOperator(['foo.php', 'bar.html', 'baz.js']);
        $index->expects($this->once())->method('getStagedFiles');
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($index);

        $ofType = new OfType(['xml', 'js']);
        $this->assertTrue($ofType->isTrue($io, $repo));
    }

    public function testStagedFalse(): void
    {
        $io    = $this->createIOMock();
        $repo  = $this->createRepositoryMock();
        $index = $this->createGitIndexOperator([]);
        $index->expects($this->once())->method('getStagedFiles');
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($index);

        $ofType = new OfType('js', ['A', 'C']);
        $this->assertFalse($ofType->isTrue($io, $repo));
    }
}
