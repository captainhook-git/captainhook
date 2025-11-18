<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\Config;

use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Condition;
use CaptainHook\App\Hooks;
use SebastianFeldmann\Git\Repository;

/**
 * Condition CustomValueIsFalsy
 *
 * With this condition, you can check if a given custom value is falsy.
 * The Action only is executed if the custom value is falsy.
 * Values considered falsy are, 0, null, empty string, empty array and false.
 *
 * Example configuration:
 *
 * <code>
 * {
 *   "action": "some-action"
 *   "conditions": [
 *     {
 *       "exec": "CaptainHook.Config.CustomValueIsFalsy",
 *       "args": ["NAME_OF_CUSTOM_VALUE"]
 *     }
 *   ]
 * }
 * </code>
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.17.2
 * @short   CaptainHook.Config.CustomValueIsFalsy
 */
class CustomValueIsFalsy extends Condition\Config
{
    /**
     * Custom config value to check
     *
     * @var string
     */
    private string $value;

    /**
     * CustomValueIsFalsy constructor
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Evaluates the condition
     *
     * @param  \CaptainHook\App\Console\IO       $io
     * @param  \SebastianFeldmann\Git\Repository $repository
     * @return bool
     */
    public function isTrue(IO $io, Repository $repository): bool
    {
        return !$this->checkCustomValue($this->value, false);
    }
}
