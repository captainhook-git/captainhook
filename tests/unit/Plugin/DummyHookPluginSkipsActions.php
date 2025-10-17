<?php

namespace CaptainHook\App\Plugin;

use CaptainHook\App\Config;
use CaptainHook\App\Runner\Hook as RunnerHook;

class DummyHookPluginSkipsActions extends DummyHookPlugin
{
    /**
     * The plugin method to set whether the hook should start skipping
     * actions.
     *
     * @var string
     */
    public static $skipStartIn = 'beforeHook';

    /**
     * Start skipping actions after the $skipStartIn method has been
     * called this many times.
     *
     * @var int
     */
    public static int $skipStartAt = 1;

    public function beforeHook(RunnerHook $hook): void
    {
        parent::beforeHook($hook);
        $this->checkSkipStart('beforeHook', $hook);
    }

    public function beforeAction(RunnerHook $hook, Config\Action $action): void
    {
        parent::beforeAction($hook, $action);
        $this->checkSkipStart('beforeAction', $hook);
    }

    public function afterAction(RunnerHook $hook, Config\Action $action): void
    {
        parent::afterAction($hook, $action);
        $this->checkSkipStart('afterAction', $hook);
    }

    public function afterHook(RunnerHook $hook): void
    {
        parent::afterHook($hook);
        $this->checkSkipStart('afterHook', $hook);
    }

    private function checkSkipStart(string $method, RunnerHook $hook): void
    {
        $property = $method . 'Called';

        if (self::$skipStartIn === $method && self::${$property} === self::$skipStartAt) {
            $hook->shouldSkipActions(true);
        }
    }
}
