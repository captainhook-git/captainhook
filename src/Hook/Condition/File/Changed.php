<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\File;

use CaptainHook\App\Console\IO;
use CaptainHook\App\Git;
use CaptainHook\App\Hook\Restriction;
use CaptainHook\App\Hooks;
use SebastianFeldmann\Git\Repository;

/**
 * Trait for all Conditions checking for changed files
 *
 * Normally we would inject that functionality into the condition but since we want to have individual classes
 * that can be referenced in a configuration file, using a Trait is a bit of a workaround.
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.27.3
 */
trait Changed
{
    /**
     * @return \CaptainHook\App\Hook\Restriction
     */
    public static function getRestriction(): Restriction
    {
        return Restriction::fromArray([Hooks::PRE_PUSH, Hooks::POST_CHECKOUT, Hooks::POST_MERGE, Hooks::POST_REWRITE]);
    }

    /**
     * @param  \CaptainHook\App\Console\IO       $io
     * @param  \SebastianFeldmann\Git\Repository $repository
     * @param  array<string>                     $diffFilter
     * @return array<string>
     */
    protected function getFiles(IO $io, Repository $repository, array $diffFilter): array
    {
        return Git\ChangedFiles::getChangedFiles($io, $repository, $diffFilter);
    }
}
