<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Hook;

use CaptainHook\App\Config;
use CaptainHook\App\Hooks;
use CaptainHook\App\Runner\Hook;
use CaptainHook\App\Runner\Util;
use SebastianFeldmann\Git;

/**
 * Base class for message aware hooks
 *
 * Message aware hooks like `prepare-commit-msg` and `commit-msg` can potentially change the commit message.
 * This class makes sure the commit message is read and written before and after any php Action that is executed.
 */
abstract class MessageAware extends Hook
{
    /**
     * Comment char '#' by default
     *
     * @var string
     */
    protected string $commentChar;

    /**
     * Path to commit message file
     *
     * @var string
     */
    protected string $file;

    /**
     * The current commit message
     *
     * @var \SebastianFeldmann\Git\CommitMessage
     */
    protected Git\CommitMessage $commitMsg;

    /**
     * Fetch the original hook arguments and message related config settings
     *
     * @return void
     */
    public function beforeHook(): void
    {
        $this->commentChar = $this->repository->getConfigOperator()->getSettingSafely('core.commentchar', '#');
        $this->file        = $this->io->getArgument(Hooks::ARG_MESSAGE_FILE);

        $this->repository->setCommitMsg(
            Git\CommitMessage::createFromFile(
                $this->file,
                $this->commentChar
            )
        );

        parent::beforeHook();
    }

    /**
     * Read the commit message from file
     *
     * @param Config\Action $action
     * @return void
     */
    public function beforeAction(Config\Action $action): void
    {
        // since every action can potentially update the commit message
        // we have to load the commit message from disk every time
        // only load the commit message from disk if a php action is executed
        if (Util::getExecType($action->getAction()) !== 'cli') {
            $this->commitMsg = Git\CommitMessage::createFromFile($this->file, $this->commentChar);
            $this->repository->setCommitMsg($this->commitMsg);
        }
        parent::beforeAction($action);
    }

    /**
     * Write the commit message to disk so git or the next action can proceed further
     *
     * @param Config\Action $action
     * @return void
     */
    public function afterAction(Config\Action $action): void
    {
        // since the action could potentially change the commit message
        // only write the commit message to disk if a php action was executed
        if (Util::getExecType($action->getAction()) !== 'cli') {
            // if the commit message was changed write it to disk
            if ($this->commitMsg->getRawContent() !== $this->repository->getCommitMsg()->getRawContent()) {
                file_put_contents($this->file, $this->repository->getCommitMsg()->getRawContent());
            }
        }
        parent::afterAction($action);
    }

    /**
     * Makes sure we do not run commit message validation for fixup commits
     *
     * @return void
     * @throws \Exception
     */
    protected function runHook(): void
    {
        $msg = $this->repository->getCommitMsg();
        if ($msg->isFixup() || $msg->isSquash()) {
            $this->io->write(' - no commit message hooks for fixup and squash commits: skipping all actions');
            return;
        }
        parent::runHook();
    }
}
