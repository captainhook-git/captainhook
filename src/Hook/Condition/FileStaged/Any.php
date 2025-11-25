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
 * Class Any
 *
 * The FileStaged condition is applicable for `pre-commit hooks.
 *
 * Example configuration:
 *
 * <code>
 * {
 *   "action": "some-action"
 *   "conditions": [
 *     {
 *       "exec": "CaptainHook.FileStaged.Any",
 *       "args": [
 *         ["list", "of", "files"]
 *       ]
 *     }
 *   ]
 * }
 * </code>
 *
 *  The file list can also be defined as comma seperated string "file1,file2,file3"
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.2.0
 */
class Any extends File\Any
{
    use File\Staged;
}
