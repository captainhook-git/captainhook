<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Git;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class DummyRepo
{
    /**
     * Path to fake git repository
     *
     * @var string
     */
    private string $path;

    /**
     * Default empty hook git dir structure
     *
     * @var array
     */
    private static array $defaultStructure = [
        'config' => '# fake git config',
        'hooks'  => [
            'pre-commit.sample' => '# fake pre-commit sample file',
            'pre-push.sample'   => '# fake pre-push sample file',
        ]
    ];

    /**
     * Fake stream directory structure
     *
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private vfsStreamDirectory $repo;

    /**
     * DummyRepo constructor
     *
     * @param array $gitDir
     * @param array $files
     */
    public function __construct(array $gitDir = [], array $files = [])
    {
        $this->repo  = vfsStream::setup('root', null, $this->setupRepo($gitDir, $files));
        $this->path  = $this->repo->url();
    }

    /**
     * Return the fake directory structure starting at '/repo-name'
     *
     * @param  array $gitDir
     * @param  array $files
     * @return array
     */
    private function setupRepo(array $gitDir, array $files): array
    {
        $dotGit = empty($gitDir) ? self::$defaultStructure : $gitDir;

        return array_merge(
            ['.git' => $dotGit],
            $files
        );
    }

    /**
     * Tells if a hook exists
     *
     * @param  string $hook
     * @return bool
     */
    public function hookExists(string $hook): bool
    {
        return $this->repo->hasChild('.git/hooks/' . $hook);
    }

    /**
     * Return the path to the fake repository
     *
     * @return string
     */
    public function getRoot(): string
    {
        return $this->path;
    }

    /**
     * Return path to fake .git dir
     *
     * @return string
     */
    public function getGitDir(): string
    {
        return $this->getRoot() . '/.git';
    }

    /**
     * Return path to the fake hook dir
     *
     * @return string
     */
    public function getHookDir(): string
    {
        return $this->getGitDir() . '/hooks';
    }
}
