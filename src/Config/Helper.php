<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Config;

/**
 * Config Helper
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.28.2
 * @internal
 */
final class Helper
{
    /**
     * Create plugin configs from the settings array
     *
     * @param  array<string, mixed> $settings
     * @return array<string, \CaptainHook\App\Config\Plugin>
     */
    public static function createPluginConfigs(array $settings): array
    {
        /* @var array<int, array<string, mixed>> $pluginSettings */
        $pluginSettings = $settings['plugins'] ?? [];
        unset($settings['plugins']);

        $plugins = [];
        foreach ($pluginSettings as $plugin) {
            $name           = (string) $plugin['plugin'];
            $options        = isset($plugin['options']) && is_array($plugin['options']) ? $plugin['options'] : [];
            $plugins[$name] = new Plugin($name, $options);
        }
        return $plugins;
    }

    /**
     * Clean up legacy run configuration settings
     *
     * @param  array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public static function cleanupRunConfig(array $settings): array
    {
        // extract the legacy settings
        $settingsToMove = [
            Settings::RUN_MODE,
            Settings::RUN_EXEC,
            Settings::RUN_PATH,
            Settings::RUN_GIT
        ];
        $config = [];
        foreach ($settingsToMove as $setting) {
            if (!empty($settings[$setting])) {
                $config[substr($setting, 4)] = $settings[$setting];
            }
            unset($settings[$setting]);
        }
        // make sure the new run configuration supersedes the legacy settings
        if (isset($settings['run']) && is_array($settings['run'])) {
            $config = array_merge($config, $settings['run']);
        }
        $settings['run'] = $config;
        return $settings;
    }

    /**
     * Extract the base settings from the given settings array
     *
     * @param  array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public static function extractBaseSettings(array $settings): array
    {
        $baseSettings = [
            Settings::ALLOW_FAILURE,
            Settings::BOOTSTRAP,
            Settings::COLORS,
            Settings::GIT_DIR,
            Settings::FAIL_ON_FIRST_ERROR,
            Settings::VERBOSITY,
            Settings::INCLUDES,
            Settings::INCLUDES_LEVEL,
            Settings::PHP_PATH,
        ];

        $config = [];
        foreach ($baseSettings as $setting) {
            if (isset($settings[$setting])) {
                $config[$setting] = $settings[$setting];
            }
        }
        return $config;
    }
}
