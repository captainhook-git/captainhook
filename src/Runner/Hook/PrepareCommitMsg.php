<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Hook;

use CaptainHook\App\Hooks;

/**
 *  Hook
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 3.1.0
 */
class PrepareCommitMsg extends MessageAware
{
    /**
     * Hook to execute
     *
     * @var string
     */
    protected string $hook = Hooks::PREPARE_COMMIT_MSG;

    /**
     * Commit mode, empty or [message|template|merge|squash|commit]
     *
     * @var string
     */
    protected string $mode;

    /**
     * Commit hash if mode is commit during -c or --amend
     *
     * @var string
     */
    protected string $hash;

    /**
     * Fetch the original hook arguments and message related config settings
     *
     * @return void
     */
    public function beforeHook(): void
    {
        $this->mode = $this->io->getArgument(Hooks::ARG_MODE);
        $this->hash = $this->io->getArgument(Hooks::ARG_HASH);

        parent::beforeHook();
    }
}
