<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Storage\File;

use Exception;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    public function testRead(): void
    {
        $this->expectException(Exception::class);

        $path = realpath(CH_PATH_FILES . '/storage/invalid-xml.txt');
        $file  = new Xml($path);
        $file->read();
    }
}
