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
use CaptainHook\App\Hook\Condition;
use SebastianFeldmann\Git\Repository;

/**
 * Class InDirectory
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
 *       "exec": "CaptainHook.FileStaged.InDirectory",
 *       "args": ["src"]
 *     }
 *   ]
 * }
 * </code>
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.6.1
 */
abstract class InDirectory extends Condition\File
{
    /**
     * Directory path to check e.g. 'src/' or 'path/To/Custom/Directory/'
     *
     * @var array<int, string>
     */
    protected array $directories;

    /**
     * --diff-filter options
     *
     * @var array<int, string>
     */
    protected array $diffFilter;

    /**
     * InDirectory constructor
     *
     * @param array<int, string>|string $directories
     * @param array<int, string>|string $diffFilter
     */
    public function __construct(array|string $directories, array|string $diffFilter = [])
    {
        $this->directories = is_array($directories) ? $directories : [$directories];
        $this->diffFilter  = FilterUtil::filterFromConfigValue($diffFilter);
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
        $filtered = $this->filterFilesByDirectory($files, $this->directories);
        return count($filtered) > 0;
    }
}
