<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\FileStaged;

use CaptainHook\App\Hook\Condition\File;

/**
 * Class ThatIs
 *
 * All FileStaged conditions are only applicable for `pre-commit` hooks.
 *
 * Example configuration:
 *
 * <code>
 * {
 *   "action": "some-action"
 *   "conditions": [
 *     {
 *       "exec": "CaptainHook.FileStaged.ThatIs",
 *       "args": [
 *         {"ofType": "php", "inDirectory": "foo/", "diff-filter": ["A", "C"]}
 *       ]
 *     }
 *   ]
 * }
 * </code>
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.16.0
 */
class ThatIs extends File\ThatIs
{
    use File\Staged;
}
