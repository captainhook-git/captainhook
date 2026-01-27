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
 *  The file list can also be defined as comma-separated string "file1,file2,file3"
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.2.0
 */
abstract class Any extends File
{
    /**
     * List of files to watch
     *
     * @var array<string>
     */
    protected array $filesToWatch;

    /**
     * --diff-filter options
     *
     * @var array<int, string>
     */
    protected array $diffFilter;

    /**
     * FileStaged constructor
     *
     * @param mixed $files
     * @param mixed $diffFilter
     */
    public function __construct(mixed $files, mixed $diffFilter = [])
    {
        $this->filesToWatch = is_array($files) ? $files : explode(',', (string) $files);
        $this->diffFilter   = FilterUtil::filterFromConfigValue($diffFilter);
    }
    /**
     * Check if any of the configured files is staged for commit
     *
     * @param  \CaptainHook\App\Console\IO       $io
     * @param  \SebastianFeldmann\Git\Repository $repository
     * @return bool
     */
    public function isTrue(IO $io, Repository $repository): bool
    {
        return $this->anyFileInHaystack($this->filesToWatch, $this->getFiles($io, $repository, $this->diffFilter));
    }
}
