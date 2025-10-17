<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Debug;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Debug;
use SebastianFeldmann\Git\Repository;

/**
 * Debug Success
 *
 * Debug hook action to test hook triggering, arguments, stdIn etc.
 * Does not crash the hook execution. The git operation will succeed.
 *
 * Example configuration:
 * <code>
 * {
 *   "action": "CaptainHook.Debug.Ok"
 * }
 * </code>
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 4.0.4
 */
class Success extends Debug
{
    /**
     * Executes the action
     *
     * @param  \CaptainHook\App\Config           $config
     * @param  \CaptainHook\App\Console\IO       $io
     * @param  \SebastianFeldmann\Git\Repository $repository
     * @param  \CaptainHook\App\Config\Action    $action
     * @return void
     * @throws \Exception
     */
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $this->debugOutput($io, $repository);
    }
}
