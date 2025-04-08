<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Console;

use CaptainHook\App\CH;
use CaptainHook\App\Console\Command as Cmd;
use CaptainHook\App\Console\Runtime\Resolver;
use Symfony\Component\Console\Application as SymfonyApplication;

/**
 * Class Application
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 0.9.0
 */
class Application extends SymfonyApplication
{
    /**
     * Path to captainhook binary
     *
     * @var string
     */
    protected string $executable;

    /**
     * Cli constructor.
     *
     * @param string $executable
     */
    public function __construct(string $executable)
    {
        $this->executable = $executable;

        parent::__construct('CaptainHook', CH::VERSION);

        $this->setDefaultCommand('list');
        $this->silenceXDebug();
    }

    /**
     * Initializes all the CaptainHook commands
     *
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function getDefaultCommands(): array
    {
        $resolver        = new Resolver($this->executable);
        $symfonyDefaults = parent::getDefaultCommands();

        return array_merge(
            array_slice($symfonyDefaults, 0, 2),
            [
                new Cmd\Install($resolver),
                new Cmd\Uninstall($resolver),
                new Cmd\Configuration($resolver),
                new Cmd\Info($resolver),
                new Cmd\Add($resolver),
                new Cmd\Disable($resolver),
                new Cmd\Enable($resolver),
                new Cmd\Hook\CommitMsg($resolver),
                new Cmd\Hook\PostCheckout($resolver),
                new Cmd\Hook\PostCommit($resolver),
                new Cmd\Hook\PostMerge($resolver),
                new Cmd\Hook\PostRewrite($resolver),
                new Cmd\Hook\PreCommit($resolver),
                new Cmd\Hook\PrepareCommitMsg($resolver),
                new Cmd\Hook\PrePush($resolver),
            ]
        );
    }

    /**
     * Append release date to version output
     *
     * @return string
     */
    public function getLongVersion(): string
    {
        return sprintf(
            '<info>%s</info> version <comment>%s</comment> %s <fg=blue>#StandWith</><fg=yellow>Ukraine</>',
            $this->getName(),
            $this->getVersion(),
            CH::RELEASE_DATE
        );
    }

    /**
     * Make sure X-Debug does not interfere with the exception handling
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    private function silenceXDebug(): void
    {
        if (function_exists('ini_set') && extension_loaded('xdebug')) {
            ini_set('xdebug.show_exception_trace', '0');
            ini_set('xdebug.scream', '0');
        }
    }
}
