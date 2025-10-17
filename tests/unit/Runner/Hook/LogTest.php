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
use CaptainHook\App\Runner\Action\Log as ActionLog;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testHasMessageForVerbosityEmptyLog(): void
    {
        $log = new Log();
        $log->addActionLog(new ActionLog(new Action("foo"), ActionLog::ACTION_SUCCEEDED, []));

        $this->assertFalse($log->hasMessageForVerbosity(IO::DEBUG));
        $this->assertFalse($log->hasMessageForVerbosity(IO::VERY_VERBOSE));
        $this->assertFalse($log->hasMessageForVerbosity(IO::VERBOSE));
        $this->assertFalse($log->hasMessageForVerbosity(IO::NORMAL));
    }

    public function testHasMessageForVerbositySingleLog(): void
    {
        $log = new Log();
        $log->addActionLog(
            new ActionLog(
                new Action("foo"),
                ActionLog::ACTION_SUCCEEDED,
                [new IO\Message('foo', true, IO::VERY_VERBOSE)]
            )
        );

        $this->assertTrue($log->hasMessageForVerbosity(IO::DEBUG), 'should have message for debug');
        $this->assertTrue($log->hasMessageForVerbosity(IO::VERY_VERBOSE), 'should have message for very verbose');
        $this->assertFalse($log->hasMessageForVerbosity(IO::VERBOSE), 'should not have message for verbose');
        $this->assertFalse($log->hasMessageForVerbosity(IO::NORMAL), 'should not have message for normal');
    }

    public function testHasMessageForVerbosityMultiLog(): void
    {
        $log = new Log();
        $log->addActionLog(
            new ActionLog(new Action("foo"), ActionLog::ACTION_SUCCEEDED, [new IO\Message('foo', true, IO::DEBUG)])
        );
        $log->addActionLog(
            new ActionLog(new Action("foo"), ActionLog::ACTION_SUCCEEDED, [new IO\Message('foo', true, IO::VERBOSE)])
        );

        $this->assertTrue($log->hasMessageForVerbosity(IO::DEBUG), 'should have message for debug');
        $this->assertTrue($log->hasMessageForVerbosity(IO::VERY_VERBOSE), 'should have message for very verbose');
        $this->assertTrue($log->hasMessageForVerbosity(IO::VERBOSE), 'should have message for verbose');
        $this->assertFalse($log->hasMessageForVerbosity(IO::NORMAL), 'should not have message for normal');
    }
}
