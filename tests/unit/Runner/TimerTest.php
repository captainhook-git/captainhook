<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class TimerTest extends TestCase
{
    public function testStartReturnsTheStartTime(): void
    {
        $timer = new Timer();

        $start = $timer->start();

        $this->assertIsFloat($start);
        $this->assertGreaterThan(0.0, $start);
    }

    public function testNotStartedTimerCanNotBeStopped(): void
    {
        $this->expectException(RuntimeException::class);
        $timer = new Timer();
        $timer->stop();
    }

    public function testTimerStatusCanBeChecked(): void
    {
        $timer = new Timer();
        $this->assertFalse($timer->isRunning());

        $timer->start();
        $this->assertTrue($timer->isRunning());

        $timer->stop();
        $this->assertFalse($timer->isRunning());
    }

    public function testStopReturnsElapsedSeconds(): void
    {
        $timer = new Timer();

        $timer->start();
        usleep(10_000); // 10ms to avoid flaky zero durations on fast machines

        $elapsed = $timer->stop();

        $this->assertIsFloat($elapsed);
        $this->assertGreaterThan(0.0, $elapsed);
        $this->assertLessThan(1.0, $elapsed, 'Timer should not take this long in a unit test');
    }
}
