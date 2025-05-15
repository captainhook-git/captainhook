<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Console\Command;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IOUtil;
use CaptainHook\App\Hook\Util;
use CaptainHook\App\Runner\Bootstrap\Util as BootstrapUtil;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Class Hook
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 0.9.0
 */
abstract class Hook extends RepositoryAware
{
    /**
     * Name of the hook to execute
     *
     * @var string
     */
    protected string $hookName;

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('hook:' . $this->hookName)
             ->setAliases([$this->hookName])
             ->setDescription('Run git ' . $this->hookName . ' hook')
             ->setHelp('This command executes the ' . $this->hookName . ' hook');

        $this->addOption(
            'bootstrap',
            'b',
            InputOption::VALUE_OPTIONAL,
            'Relative path from your config file to your bootstrap file'
        );
        $this->addOption(
            'input',
            'i',
            InputOption::VALUE_OPTIONAL,
            'Original hook stdIn'
        );
    }

    /**
     * Execute the command
     *
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->shouldHooksBeSkipped()) {
            $output->writeLn('all hooks were skipped because of the environment variable CAPTAINHOOK_SKIP_HOOKS or CI');
            return 0;
        }

        $io         = $this->getIO($input, $output);
        $config     = $this->createConfig($input, true, ['git-directory', 'bootstrap']);
        $repository = $this->createRepository(dirname($config->getGitDirectory()));

        // use ansi coloring if available and not disabled in captainhook.json
        $output->setDecorated($output->isDecorated() && $config->useAnsiColors());
        // use the configured verbosity to manage general output verbosity
        $output->setVerbosity(IOUtil::mapConfigVerbosity($config->getVerbosity()));

        try {
            $this->handleBootstrap($config);

            $class = '\\CaptainHook\\App\\Runner\\Hook\\' . Util::getHookCommand($this->hookName);
            /** @var \CaptainHook\App\Runner\Hook $hook */
            $hook  = new $class($io, $config, $repository);
            $hook->run();
            return 0;
        } catch (Throwable $e) {
            if ($output->isDebug()) {
                throw $e;
            }
            return 1;
        }
    }

    /**
     * If CaptainHook is executed via PHAR this handles the bootstrap file inclusion
     *
     * @param \CaptainHook\App\Config $config
     */
    private function handleBootstrap(Config $config): void
    {
        // we only have to care about bootstrapping PHAR builds because for
        // Composer installs the bootstrapping is already done in the bin script
        if ($this->resolver->isPharRelease()) {
            // check the custom and default autoloader
            $bootstrapFile = BootstrapUtil::validateBootstrapPath($this->resolver->isPharRelease(), $config);
            // since the phar has its own autoloader we don't need to do anything
            // if the bootstrap file is not actively set
            if (empty($bootstrapFile)) {
                return;
            }
            // the bootstrap file exists so lets load it
            try {
                require $bootstrapFile;
            } catch (Throwable $t) {
                throw new RuntimeException(
                    'Bootstrapping failed:' . PHP_EOL .
                    '  Please fix your bootstrap file `' . $bootstrapFile . '`' . PHP_EOL
                );
            }
        }
    }

    /**
     * Indicates if hooks should be skipped
     *
     * Either because of CI environment or the SKIP environment variable is set.
     *
     * @return bool
     */
    private function shouldHooksBeSkipped(): bool
    {
        foreach (['CAPTAINHOOK_SKIP_HOOKS', 'CI'] as $envVar) {
            $skip = (int) ($_SERVER[$envVar] ?? 0);
            if ($skip === 1) {
                return true;
            }
        }
        return false;
    }
}
