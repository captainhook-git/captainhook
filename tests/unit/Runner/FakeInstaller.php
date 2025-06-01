<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner;

use CaptainHook\App\Storage\File;

class FakeInstaller extends Installer
{
    public function checkSymlink(File $file): void
    {
        $this->checkForBrokenSymlink($file);
    }
}
