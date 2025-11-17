<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner;

use CaptainHook\App\Hook;
use RuntimeException;

/**
 * Class Shorthand
 *
 * Defines some shorthands that can be used in the configuration file to not
 * clutter the configuration with the full classnames.
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.26.0
 */
class Shorthand
{
    /**
     * Shorthand to action mapping
     *
     * @var array<string, array<string, array<string, string>>>
     */
    private static array $map = [
        'action'    => [
            'branch'  => [
                'ensurenaming'                       => Hook\Branch\Action\EnsureNaming::class,
                'preventpushoffixupandsquashcommits' => Hook\Branch\Action\BlockFixupAndSquashCommits::class,
            ],
            'debug'   => [
                'fail' => Hook\Debug\Failure::class,
                'ok'   => Hook\Debug\Success::class,
            ],
            'file'    => [
                'blocksecrets'        => Hook\Diff\Action\BlockSecrets::class,
                'doesnotcontainregex' => Hook\File\Action\DoesNotContainRegex::class,
                'isnotempty'          => Hook\File\Action\IsNotEmpty::class,
                'maxsize'             => Hook\File\Action\MaxSize::class,
            ],
            'message' => [
                'injectissuekeyfrombranch' => Hook\Message\Action\InjectIssueKeyFromBranch::class,
                'cacheonfail    '          => Hook\Message\Action\CacheOnFail::class,
                'mustfollowbeamsrules'     => Hook\Message\Action\Beams::class,
                'mustcontainsregex'        => Hook\Message\Action\Regex::class,
                'preparefromfile'          => Hook\Message\Action\PrepareFromFile::class,
                'prepare'                  => Hook\Message\Action\Prepare::class,
            ],
            'notify'  => [
                'gitnotify' => Hook\Notify\Action\Notify::class,
            ],
        ],
        'condition' => [
            'config'    => [
                'customvalueistruthy' => Hook\Condition\Config\CustomValueIsTruthy::class,
                'customvalueisfalsy'  => Hook\Condition\Config\CustomValueIsFalsy::class,
            ],
            'filechanged' => [
                'any' => Hook\Condition\FileChanged\Any::class,
                'all' => Hook\Condition\FileChanged\All::class,
            ],
            'filestaged'  => [
                'all'         => Hook\Condition\FileStaged\All::class,
                'any'         => Hook\Condition\FileStaged\Any::class,
                'indirectory' => Hook\Condition\FileStaged\InDirectory::class,
                'oftype'      => Hook\Condition\FileStaged\OfType::class,
                'thatis'      => Hook\Condition\FileStaged\ThatIs::class,
            ],
            'status'      => [
                'onbranch'            => Hook\Condition\Branch\On::class,
                'onmatchingbranch'    => Hook\Condition\Branch\OnMatching::class,
                'notonbranch'         => Hook\Condition\Branch\NotOn::class,
                'notonmatchingbranch' => Hook\Condition\Branch\NotOnMatching::class,
            ]
        ]
    ];

    /**
     * Check if a configured action or condition value is actually a shorthand for an internal action
     *
     * @param  string $shorthand
     * @return bool
     */
    public static function isShorthand(string $shorthand): bool
    {
        return preg_match('#^captainhook\.[a-z]+\.[a-z]+$#i', $shorthand);
    }

    /**
     * Return the matching action class for given action shorthand
     *
     * @param  string $shorthand
     * @return string
     */
    public static function getActionClass(string $shorthand): string
    {
        return Shorthand::getClass('action', $shorthand);
    }

    /**
     * Return the matching condition class for given condition shorthand
     *
     * @param  string $shorthand
     * @return string
     */
    public static function getConditionClass(string $shorthand): string
    {
        return Shorthand::getClass('condition', $shorthand);
    }

    /**
     * Returns the matching class for shorthand
     *
     * @param  string $type
     * @param  string $shorthand
     * @return string
     */
    private static function getClass(string $type, string $shorthand): string
    {
        $path = explode('.', strtolower($shorthand));
        if (count($path) !== 3) {
            throw new RuntimeException('Invalid ' . $type . ' shorthand: ' . $shorthand);
        }
        [$trigger, $group, $name] = $path;
        if (!isset(self::$map[$type][$group])) {
            throw new RuntimeException('Invalid ' . $type . ' group: ' . $group);
        }
        if (!isset(self::$map[$type][$group][$name])) {
            throw new RuntimeException('Invalid ' . $type . ' => ' . $name);
        }
        return self::$map[$type][$group][$name];
    }
}
