<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Plugin\Hook;

use CaptainHook\App\Config;
use CaptainHook\App\Plugin\Hook as HookPlugin;
use CaptainHook\App\Runner\Hook as RunnerHook;

/**
 * Simple demo plugin
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.28.3
 */
class SimplePlugin extends Base implements HookPlugin
{
    public function beforeHook(RunnerHook $hook): void
    {
        $stuff = $this->plugin->getOptions()->get('stuff');
        $this->io->write("Do {$stuff} before {$hook->getName()} runs");
    }

    public function beforeAction(RunnerHook $hook, Config\Action $action): void
    {
        $stuff = $this->plugin->getOptions()->get('stuff');
        $this->io->write("Do {$stuff} before action {$action->getAction()} runs");
    }

    public function afterAction(RunnerHook $hook, Config\Action $action): void
    {
        $stuff = $this->plugin->getOptions()->get('stuff');
        $this->io->write("Do {$stuff} after action {$action->getAction()} runs");
    }

    public function afterHook(RunnerHook $hook): void
    {
        $stuff = $this->plugin->getOptions()->get('stuff');
        $this->io->write("Do {$stuff} after {$hook->getName()} runs");
    }
}
