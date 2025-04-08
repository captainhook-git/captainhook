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

use CaptainHook\App\Console\Runtime\Resolver;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Command
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.0.0
 */
abstract class Command extends SymfonyCommand
{
    /**
     * Input output handler
     *
     * @var \CaptainHook\App\Console\IO
     */
    private $io;

    /**
     * Runtime resolver
     *
     * @var \CaptainHook\App\Console\Runtime\Resolver
     */
    protected Resolver $resolver;

    /**
     * Command constructor
     *
     * @param \CaptainHook\App\Console\Runtime\Resolver $resolver
     */
    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
        parent::__construct();
    }

    /**
     * IO setter
     *
     * @param \CaptainHook\App\Console\IO $io
     */
    public function setIO(IO $io): void
    {
        $this->io = $io;
    }

    /**
     * IO interface getter
     *
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return \CaptainHook\App\Console\IO
     */
    public function getIO(InputInterface $input, OutputInterface $output): IO
    {
        if (null === $this->io) {
            $this->io = new IO\DefaultIO($input, $output, $this->getHelperSet());
        }
        return $this->io;
    }
}
