<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App;

use CaptainHook\App\Config\Helper;
use CaptainHook\App\Config\Run;
use CaptainHook\App\Storage\File;
use InvalidArgumentException;
use SebastianFeldmann\Camino\Check;

/**
 * Class Config
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 0.9.0
 * @internal
 */
class Config
{
    /**
     * Path to the config file
     *
     * @var string
     */
    private string $path;

    /**
     * Was the given path absolute or relative?
     *
     * @var bool
     */
    private bool $pathProvidedIsAbsolute = false;

    /**
     * Does the config file exist?
     *
     * @var bool
     */
    private bool $fileExists;

    /**
     * CaptainHook settings
     *
     * @var array<string, string>
     */
    private array $settings;

    /**
     * All options related to running CaptainHook
     *
     * @var \CaptainHook\App\Config\Run
     */
    private Run $runConfig;

    /**
     * List of users custom settings
     *
     * @var array<string, mixed>
     */
    private array $custom = [];

    /**
     * List of plugins
     *
     * @var array<string, \CaptainHook\App\Config\Plugin>
     */
    private array $plugins = [];

    /**
     * List of hook configs
     *
     * @var array<string, \CaptainHook\App\Config\Hook>
     */
    private array $hooks = [];

    /**
     * Config constructor
     *
     * @param string               $path
     * @param bool                 $fileExists
     * @param array<string, mixed> $settings
     */
    public function __construct(string $path, bool $fileExists = false, array $settings = [])
    {
        $this->initializeHookConfigs();
        $this->setupSettings($settings);
        $this->setupPlugins($settings);
        $this->setupCustom($settings);
        $this->setupRunConfig($settings);

        // remember that the path given was absolute, important for 'install' handling
        if (Check::isAbsolutePath($path)) {
            $this->pathProvidedIsAbsolute = true;
        }
        $this->path       = File::makePathAbsolute($path);
        $this->fileExists = $fileExists;
    }

    /**
     * Set up base config settings
     *
     * Basically everything that is not a run-config, plugin-config, or custom-config settings.
     *
     * @param  array<string, mixed> $settings
     * @return void
     */
    private function setupSettings(array $settings): void
    {
        $this->settings = Helper::extractBaseSettings($settings);
    }

    /**
     * Extract custom settings from Captain Hook ones
     *
     * @param  array<string, mixed> $settings
     * @return void
     */
    private function setupCustom(array $settings): void
    {
        /* @var array<string, mixed> $custom */
        $this->custom = $settings['custom'] ?? [];
    }

    /**
     * Setup all configured plugins
     *
     * @param  array<string, mixed> $settings
     * @return void
     */
    private function setupPlugins(array $settings): void
    {
        $this->plugins = Helper::createPluginConfigs($settings);
    }

    /**
     * Extract all run-related settings into a run configuration
     *
     * @param  array<string, mixed> $settings
     * @return void
     */
    private function setupRunConfig(array $settings): void
    {
        $settings        = Helper::cleanupRunConfig($settings);
        $this->runConfig = new Run($settings['run'] ?? []);
    }

    /**
     * Initialize all hook configs
     */
    private function initializeHookConfigs(): void
    {
        foreach (Hooks::getValidHooks() as $hook => $value) {
            $this->hooks[$hook] = new Config\Hook($hook);
        }
    }

    /**
     * Is the configuration loaded from a file?
     *
     * @return bool
     */
    public function isLoadedFromFile(): bool
    {
        return $this->fileExists;
    }

    /**
     * Are actions allowed to fail without stopping the git operation
     *
     * @return bool
     */
    public function isFailureAllowed(): bool
    {
        return (bool) ($this->settings[Config\Settings::ALLOW_FAILURE] ?? false);
    }

    /**
     * @param  string $hook
     * @param  bool   $withVirtual if true, also check if hook is enabled through any enabled virtual hook
     * @return bool
     */
    public function isHookEnabled(string $hook, bool $withVirtual = true): bool
    {
        // either this hook is explicitly enabled
        $hookConfig = $this->getHookConfig($hook);
        if ($hookConfig->isEnabled()) {
            return true;
        }

        // or any virtual hook that triggers it is enabled
        if ($withVirtual && Hooks::triggersVirtualHook($hookConfig->getName())) {
            $virtualHookConfig = $this->getHookConfig(Hooks::getVirtualHook($hookConfig->getName()));
            if ($virtualHookConfig->isEnabled()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Path getter
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Indicates if the path provided to the configuration file was absolute or relative
     *
     * Since we only work with absolute paths, we need to now if we should do some relative
     * path shenanigans during installation or not.
     */
    public function isProvidedPathAbsolute(): bool
    {
        return $this->pathProvidedIsAbsolute;
    }

    /**
     * Return git directory path if configured, CWD/.git if not
     *
     * @return string
     */
    public function getGitDirectory(): string
    {
        if (empty($this->settings[Config\Settings::GIT_DIR])) {
            return getcwd() . '/.git';
        }

        // if the repo path is absolute, use it otherwise create an absolute path relative to the configuration file
        return Check::isAbsolutePath($this->settings[Config\Settings::GIT_DIR])
            ? $this->settings[Config\Settings::GIT_DIR]
            : dirname($this->path) . '/' . $this->settings[Config\Settings::GIT_DIR];
    }

    /**
     * Return bootstrap file if configured, CWD/vendor/autoload.php by default
     *
     * @param  string $default
     * @return string
     */
    public function getBootstrap(string $default = 'vendor/autoload.php'): string
    {
        return !empty($this->settings[Config\Settings::BOOTSTRAP])
            ? $this->settings[Config\Settings::BOOTSTRAP]
            : $default;
    }

    /**
     * Return the configured verbosity
     *
     * @return string
     */
    public function getVerbosity(): string
    {
        return !empty($this->settings[Config\Settings::VERBOSITY])
            ? $this->settings[Config\Settings::VERBOSITY]
            : 'normal';
    }

    /**
     * Should the output use ansi colors
     *
     * @return bool
     */
    public function useAnsiColors(): bool
    {
        return (bool) ($this->settings[Config\Settings::COLORS] ?? true);
    }

    /**
     * Get configured php-path
     *
     * @return string
     */
    public function getPhpPath(): string
    {
        return (string) ($this->settings[Config\Settings::PHP_PATH] ?? '');
    }

    /**
     * Get run configuration
     *
     * @return \CaptainHook\App\Config\Run
     */
    public function getRunConfig(): Run
    {
        return $this->runConfig;
    }

    /**
     * Returns the users custom config values
     *
     * @return array<mixed>
     */
    public function getCustomSettings(): array
    {
        return $this->custom;
    }

    /**
     * Whether to abort the hook as soon as any action has errored. Default is true.
     * Otherwise, all actions get executed (even if some of them have failed), and
     * finally, a non-zero exit code is returned if any action has errored.
     *
     * @return bool
     */
    public function failOnFirstError(): bool
    {
        return (bool) ($this->settings[Config\Settings::FAIL_ON_FIRST_ERROR] ?? true);
    }

    /**
     * Return config for given hook
     *
     * @param  string $hook
     * @return \CaptainHook\App\Config\Hook
     * @throws \InvalidArgumentException
     */
    public function getHookConfig(string $hook): Config\Hook
    {
        if (!Hook\Util::isValid($hook)) {
            throw new InvalidArgumentException('Invalid hook name: ' . $hook);
        }
        return $this->hooks[$hook];
    }

    /**
     * Return hook configs
     *
     * @return array<string, \CaptainHook\App\Config\Hook>
     */
    public function getHookConfigs(): array
    {
        return $this->hooks;
    }

    /**
     * Returns a hook config containing all the actions to execute
     *
     * Returns all actions from the triggered hook but also any actions of virtual hooks that might be triggered.
     * E.g. 'post-rewrite' or 'post-checkout' trigger the virtual/artificial 'post-change' hook.
     * Virtual hooks are special hooks to simplify configuration.
     *
     * @param  string $hook
     * @return \CaptainHook\App\Config\Hook
     */
    public function getHookConfigToExecute(string $hook): Config\Hook
    {
        $config     = new Config\Hook($hook, true);
        $hookConfig = $this->getHookConfig($hook);
        $config->addAction(...$hookConfig->getActions());
        if (Hooks::triggersVirtualHook($hookConfig->getName())) {
            $vHookConfig = $this->getHookConfig(Hooks::getVirtualHook($hookConfig->getName()));
            if ($vHookConfig->isEnabled()) {
                $config->addAction(...$vHookConfig->getActions());
            }
        }
        return $config;
    }

    /**
     * Return plugins
     *
     * @return Config\Plugin[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Return config array to write to disc
     *
     * @return array<string, mixed>
     */
    public function getJsonData(): array
    {
        $data   = [];
        $config = $this->getConfigJsonData();

        if (!empty($config)) {
            $data['config'] = $config;
        }

        foreach (Hooks::getValidHooks() as $hook => $value) {
            if ($this->hooks[$hook]->isEnabled() || $this->hooks[$hook]->hasActions()) {
                $data[$hook] = $this->hooks[$hook]->getJsonData();
            }
        }
        return $data;
    }

    /**
     * Build the "config" JSON section of the configuration file
     *
     * @return array<string, mixed>
     */
    private function getConfigJsonData(): array
    {
        $config = !empty($this->settings) ? $this->settings : [];

        $runConfigData = $this->runConfig->getJsonData();
        if (!empty($runConfigData)) {
            $config['run'] = $runConfigData;
        }
        if (!empty($this->plugins)) {
            $config['plugins'] = $this->getPluginsJsonData();
        }
        if (!empty($this->custom)) {
            $config['custom'] = $this->custom;
        }
        return $config;
    }

    /**
     * Collect and return plugin json data for all plugins
     *
     * @return array<int, mixed>
     */
    private function getPluginsJsonData(): array
    {
        $plugins = [];
        foreach ($this->plugins as $plugin) {
            $plugins[] = $plugin->getJsonData();
        }
        return $plugins;
    }
}
