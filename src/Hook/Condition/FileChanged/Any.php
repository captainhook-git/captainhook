<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\FileChanged;

use CaptainHook\App\Hook\Condition\File;

/**
 * Class Any
 *
 * The FileChange condition is applicable for `post-merge` and `post-checkout` hooks.
 * For example, it can be used to trigger an automatic composer install if the composer.json
 * or `composer.lock` file is changed during a checkout or merge.
 *
 * Example configuration:
 *
 * <code>
 * {
 *   "action": "some-action"
 *   "conditions": [
 *     {
 *       "exec": "CaptainHook.FileChanged.Any",
 *       "args": [
 *         ["list", "of", "files"]
 *       ]
 *     }
 *   ]
 * }
 *  </code>
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 4.2.0
 */
class Any extends File\Any
{
    use File\Changed;
}
