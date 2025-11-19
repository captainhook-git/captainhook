<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Console\IO;

use CaptainHook\App\Console\IO;

class TestIO implements IO
{
    private array $log = [];
    private array $err = [];

    /**
     * @inheritDoc
     */
    public function getArguments(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getArgument(string $name, string $default = ''): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getStandardInput(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function isInteractive()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isVerbose()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isVeryVerbose()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isDebug()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function write($messages, $newline = true, $verbosity = self::NORMAL)
    {
        $this->log[] = (is_array($messages) ? implode(PHP_EOL, $messages) : $messages) . ($newline ? PHP_EOL : '');
    }

    public function getLog(): array
    {
        return $this->log;
    }

    /**
     * @inheritDoc
     */
    public function writeError($messages, $newline = true, $verbosity = self::NORMAL)
    {
        $this->err[] = $messages . ($newline ? PHP_EOL : '');
    }

    public function getErr(): array
    {
        return $this->err;
    }

    /**
     * @inheritDoc
     */
    public function ask($question, $default = null)
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function askConfirmation($question, $default = true)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function askAndValidate($question, $validator, $attempts = null, $default = null)
    {
        return '';
    }
}
