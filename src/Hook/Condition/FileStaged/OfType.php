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
 * Class OfType
 *
 * All FileStaged conditions are only applicable for `pre-commit` hooks.
 * The diff filter argument is optional.
 *
 * Example configuration:
 *
 * <code>
 * {
 *   "action": "some-action"
 *   "conditions": [
 *     {
 *       "exec": "CaptainHook.FileStaged.OfType",
 *       "args": [
 *         "php",
 *         ["A", "C"]
 *       ]
 *     }
 *   ]
 * }
 * </code>
 *
 * Multiple types can be configured using a comma separated string or an array
 *  - "php,html,xml"
 *  - ["php", "html", "xml"]
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.0.0
 */
class OfType extends File\OfType
{
    use File\Staged;
}
