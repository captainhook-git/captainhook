<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Storage;

use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FileTest extends TestCase
{
    public function testGetPath(): void
    {
        $file = new File(__FILE__);

        $this->assertEquals(__FILE__, $file->getPath());
    }

    public function testRead(): void
    {
        $file    = new File(__FILE__);
        $content = $file->read();

        $this->assertStringContainsString('<?php', $content);
    }

    public function testReadFail(): void
    {
        $this->expectException(Exception::class);

        $file = new File(__FILE__ . '.absent');
        $file->read();
    }

    public function testWrite(): void
    {
        $tmpDir = sys_get_temp_dir();
        $path   = tempnam($tmpDir, 'foo');
        $file   = new File($path);
        $file->write('foo');

        $this->assertEquals('foo', file_get_contents($path));
        $this->assertTrue(unlink($path));
    }

    public function testIsLink(): void
    {
        $tmpDir = sys_get_temp_dir();
        $link   = $tmpDir . '/fooLink';
        $target = tempnam($tmpDir, 'bar');

        symlink($target, $link);

        $file = new File($link);
        $this->assertTrue($file->isLink());
        $this->assertEquals($target, $file->linkTarget());

        $this->assertTrue(unlink($link));
        $this->assertTrue(unlink($target));
    }

    public function testLinkTarget(): void
    {
        $this->expectException(RuntimeException::class);

        $file = new File(__FILE__);
        $file->linkTarget();
    }

    public function testWriteFailNoDir(): void
    {
        $this->expectException(Exception::class);

        $path   = __FILE__ . DIRECTORY_SEPARATOR . 'foo.txt';
        $file   = new File($path);
        $file->write('foo');
    }

    public function testNoWritePermission(): void
    {
        $this->expectException(Exception::class);

        vfsStream::setup('exampleDir', 0000);
        $file = new File(vfsStream::url('exampleDir'));
        $file->write('test');
    }

    public function testCantCreateDirectory(): void
    {
        $this->expectException(Exception::class);

        vfsStream::setup('exampleDir', 0000);
        $baseDir = vfsStream::url('exampleDir');

        $path = $baseDir . '/foo/bar.txt';
        $file = new File($path);
        $file->write('test');
    }

    public function testLeavesAbsolutePathsUntouched(): void
    {
        $path = '/foo/bar.txt';
        $abs  = File::makePathAbsolute($path);

        $this->assertEquals($path, $abs);
    }
}
