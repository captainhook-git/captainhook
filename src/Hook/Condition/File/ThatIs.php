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
abstract class ThatIs extends File
{
    /**
     * Directory path to check e.g. 'src/' or 'path/To/Custom/Directory/'
     *
     * @var string[]
     */
    private array $directories;

    /**
     * File type to check e.g. 'php' or 'html'
     *
     * @var string[]
     */
    private array $suffixes;

    /**
     * --diff-filter options
     *
     * @var array<int, string>
     */
    protected array $diffFilter;

    /**
     * OfType constructor
     *
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->setupDirectories($options);
        $this->setupSuffixes($options);

        $diffFilter       = $options['diffFilter'] ?? [];
        $this->diffFilter = FilterUtil::sanitize(is_array($diffFilter) ? $diffFilter : str_split($diffFilter));
    }

    /**
     * Read directories from option
     *
     * @param  array<string, array<string>|string> $options
     * @return void
     */
    private function setupDirectories(array $options): void
    {
        if (empty($options['inDirectory'])) {
            $this->directories = [];
            return;
        }
        $this->directories = is_array($options['inDirectory']) ? $options['inDirectory'] : [$options['inDirectory']];
    }

    /**
     * Read filetypes from options
     *
     * @param  array<string, array<string>|string> $options
     * @return void
     */
    private function setupSuffixes(array $options): void
    {
        if (empty($options['ofType'])) {
            $this->suffixes = [];
            return;
        }
        $this->suffixes = is_array($options['ofType']) ? $options['ofType'] : [$options['ofType']];
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
        $files = $this->getFiles($io, $repository, $this->diffFilter);
        $files = $this->filterFilesByDirectory($files, $this->directories);
        $files = $this->filterFilesByType($files, $this->suffixes);
        return count($files) > 0;
    }
}
