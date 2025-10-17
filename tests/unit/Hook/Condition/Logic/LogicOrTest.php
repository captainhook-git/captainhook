<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\Logic;

use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Hook\Condition;
use CaptainHook\App\Mockery as AppMockery;
use PHPUnit\Framework\TestCase;

class LogicOrTest extends TestCase
{
    use IOMockery;
    use AppMockery;

    public function testLogicOrReturnsTrueWithOneFailure(): void
    {
        $io         = $this->createIOMock();
        $repository = $this->createRepositoryMock();
        $true       = $this->getMockBuilder(Condition::class)->getMock();
        $false      = $this->getMockBuilder(Condition::class)->getMock();

        $true->method('isTrue')->willReturn(true);
        $false->method('isTrue')->willReturn(false);

        $condition = LogicOr::fromConditionsArray([$false, $true]);

        $this->assertTrue($condition->isTrue($io, $repository));
    }

    public function testLogicOrReturnsFalseWithAllFailing(): void
    {
        $io         = $this->createIOMock();
        $repository = $this->createRepositoryMock();
        $false      = $this->getMockBuilder(Condition::class)->getMock();

        $false->method('isTrue')->willReturn(false);

        $condition = LogicOr::fromConditionsArray([$false, $false, $false]);

        $this->assertFalse($condition->isTrue($io, $repository));
    }
}
