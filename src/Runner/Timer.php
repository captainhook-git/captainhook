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

use RuntimeException;

/**
 * Timer
 *
 * Measures hook and action execution times.
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.28.0
 */
class Timer
{
    private float $start = 0.0;
    private float $stop = 0.0;

    /**
     * Start the timer
     *
     * @return float
     */
    public function start(): float
    {
        $this->start = microtime(true);
        return $this->start;
    }

    /**
     * Stop the timer and calculate the elapsed time.
     *
     * @return float The time elapsed in seconds.
     */
    public function stop(): float
    {
        if ($this->start === 0.0) {
            throw new RuntimeException('Timer not started');
        }
        $this->stop = microtime(true);
        return $this->stop - $this->start;
    }

    /**
     * Determine if the timer is currently running
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->start > 0.0 && $this->stop === 0.0;
    }

    /**
     * Creates a new timer, starts the timer, and returns the instance.
     *
     * @return \CaptainHook\App\Runner\Timer.
     */
    public static function createAndStart(): self
    {
        $timer = new self();
        $timer->start();
        return $timer;
    }
}
