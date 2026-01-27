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
 * Class All
 *
 * The FileStaged condition is applicable for `pre-commit` hooks.
 * It checks if all configured files are staged for commit.
 *
 * Example configuration:
 *
 * <code>
 * {
 *   "action": "some-action"
 *   "conditions": [
 *     {
 *       "exec": "CaptainHook.FileStaged.All",
 *       "args": [
 *         ["list", "of", "files"]
 *       ]
 *     }
 *   ]
 * }
 * </code>
 *
 * The file list can also be defined as comma-separated string "file1,file2,file3".
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.2.0
 */
class All extends File\All
{
    use File\Staged;
}
