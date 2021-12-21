<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Plugin\Hook\Exception;

use CaptainHook\App\Exception\CaptainHookException;
use RuntimeException;

/**
 * Class ActionChangedFiles
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhookphp/captainhook
 * @since   Class available since Release 5.11.0.
 */
class ActionChangedFiles extends RuntimeException implements CaptainHookException
{
}
