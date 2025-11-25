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
 * Class All
 *
 * The FileChange condition is applicable for `post-merge` and `post-checkout` hooks.
 * It checks if all configured files are updated within the last change set.
 *
 * Example configuration:
 *
 *  <code>
 *  {
 *    "action": "some-action"
 *    "conditions": [
 *      {
 *        "exec": "CaptainHook.FileChanged.All",
 *        "args": [
 *          ["list", "of", "files"]
 *        ]
 *      }
 *    ]
 *  }
 *  </code>
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 4.2.0
 */
class All extends File\All
{
    use File\Changed;
}
