<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace CaptainHook\App\Hook\Condition\Branch;

use CaptainHook\App\Console\IO;
use SebastianFeldmann\Git\Repository;

/**
 * NotOnMatching Branch condition
 *
 * Example configuration:
 * <code>
 * {
 *   "action": "some-action",
 *   "conditions": [
 *     {
 *       "exec": "CaptainHook.Status.NotOnMatchingBranch",
 *       "args": ["#^branches-names/not-matching[0-9]+-this-regex$#i"]
 *     }
 *   ]
 * }
 * </code>
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.20.2
 */
class NotOnMatching extends Name
{
    /**
     * Check if the current branch is matched by the configured regex
     *
     * @param  \CaptainHook\App\Console\IO       $io
     * @param  \SebastianFeldmann\Git\Repository $repository
     * @return bool
     */
    public function isTrue(IO $io, Repository $repository): bool
    {
        return preg_match($this->name, trim($repository->getInfoOperator()->getCurrentBranch())) === 0;
    }
}
