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

class JsonTest extends TestCase
{
    public function testReadInvalid(): void
    {
        $this->expectException(Exception::class);

        $path = realpath(CH_PATH_FILES . '/config/invalid.json');
        $json = new Json($path);
        $json->readAssoc();
    }

    public function testReadAssoc(): void
    {
        $path = realpath(CH_PATH_FILES . '/storage/test.json');
        $json = new Json($path);
        $data = $json->readAssoc();

        $this->assertEquals('bar', $data['foo']);
    }

    public function testWrite(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'json');
        $json = new Json($path);
        $data = ['foo' => 'bar'];
        $json->write($data);

        $json = file_get_contents($path);
        $load = json_decode($json, true);

        unlink($path);
        $this->assertEquals('bar', $load['foo']);
    }
}
