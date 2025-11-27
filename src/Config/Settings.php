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

class Settings
{
    public const ALLOW_FAILURE       = 'allow-failure';
    public const BOOTSTRAP           = 'bootstrap';
    public const COLORS              = 'ansi-colors';
    public const CUSTOM              = 'custom';
    public const GIT_DIR             = 'git-directory';
    public const INCLUDES            = 'includes';
    public const INCLUDES_LEVEL      = 'includes-level';
    public const LABEL               = 'label';
    public const RUN_EXEC            = 'run-exec';
    public const RUN_MODE            = 'run-mode';
    public const RUN_PATH            = 'run-path';
    public const RUN_GIT             = 'run-git';
    public const PHP_PATH            = 'php-path';
    public const VERBOSITY           = 'verbosity';
    public const FAIL_ON_FIRST_ERROR = 'fail-on-first-error';
}
