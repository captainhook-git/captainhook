<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\File;

use CaptainHook\App\Console\IO;
use CaptainHook\App\Git\Diff\FilterUtil;
use CaptainHook\App\Hook\Condition\File;
use SebastianFeldmann\Git\Repository;

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
abstract class OfType extends File
{
    /**
     * File type to check e.g. 'php' or 'html'
     *
     * @var array<int, string>
     */
    protected array $suffixes;

    /**
     * --diff-filter option
     *
     * @var array<int, string>
     */
    protected array $diffFilter;

    /**
     * OfType constructor
     *
     * @param array<int, string>|string $types
     * @param array<int, string>|string $filter
     */
    public function __construct(array|string $types, array|string $filter = [])
    {
        $this->suffixes   = is_array($types) ? $types : explode(',', $types);
        $this->diffFilter = FilterUtil::filterFromConfigValue($filter);
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
        $files    = $this->getFiles($io, $repository, $this->diffFilter);
        $filtered = $this->filterFilesByType($files, $this->suffixes);
        return count($filtered) > 0;
    }
}
