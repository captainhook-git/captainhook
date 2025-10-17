<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Hook;

use CaptainHook\App\Config\Action;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as AppMockery;
use CaptainHook\App\Runner\Action\Log as ActionLog;
use CaptainHook\App\Runner\Hook as HookRunner;
use PHPUnit\Framework\TestCase;

class PrinterTest extends TestCase
{
    use AppMockery;
    use IOMockery;

    public function testHookEndDebug(): void
    {
        $io = $this->createIOMock();
        $io->method('isDebug')->willReturn(true);
        $io->expects($this->atLeast(2))->method('write');

        $log = new Log();
        $log->addActionLog(
            new ActionLog(
                new Action("foo"),
                ActionLog::ACTION_SUCCEEDED,
                [new IO\Message('foo', true, IO::DEBUG)]
            )
        );

        $printer = new Printer($io);
        $printer->hookEnded(HookRunner::HOOK_SUCCEEDED, $log, 0.25);
    }

    public function testHookEndVeryVerbose(): void
    {
        $io = $this->createIOMock();
        $io->method('isDebug')->willReturn(false);
        $io->method('isVeryVerbose')->willReturn(true);
        $io->expects($this->atLeast(2))->method('write');

        $log = new Log();
        $log->addActionLog(
            new ActionLog(
                new Action("foo"),
                ActionLog::ACTION_SUCCEEDED,
                [new IO\Message('foo', true, IO::VERY_VERBOSE)]
            )
        );

        $printer = new Printer($io);
        $printer->hookEnded(HookRunner::HOOK_SUCCEEDED, $log, 0.25);
    }

    public function testHookEndVerbose(): void
    {
        $io = $this->createIOMock();
        $io->method('isDebug')->willReturn(false);
        $io->method('isVeryVerbose')->willReturn(false);
        $io->method('isVerbose')->willReturn(true);
        $io->expects($this->atLeast(2))->method('write');

        $log = new Log();
        $log->addActionLog(
            new ActionLog(
                new Action("foo"),
                ActionLog::ACTION_SUCCEEDED,
                [new IO\Message('foo', true, IO::VERBOSE), new IO\Message('foo', true, IO::VERY_VERBOSE)]
            )
        );

        $printer = new Printer($io);
        $printer->hookEnded(HookRunner::HOOK_SUCCEEDED, $log, 0.25);
    }
}
