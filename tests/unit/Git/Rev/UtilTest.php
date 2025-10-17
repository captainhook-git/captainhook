<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Git\Rev;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class UtilTest extends TestCase
{
    public function testIsZeroHash()
    {
        $this->assertTrue(Util::isZeroHash('00000000000000000'));
        $this->assertTrue(Util::isZeroHash('0000000'));
        $this->assertFalse(Util::isZeroHash('ef65da'));
    }

    public function testExtractBranchInfoDefaultRemote()
    {
        $info = Util::extractBranchInfo('foo-bar-baz');
        $this->assertEquals('origin', $info['remote']);
        $this->assertEquals('foo-bar-baz', $info['branch']);
    }

    public function testExtractBranchInfoWithRef()
    {
        $info = Util::extractBranchInfo('refs/origin/foo');
        $this->assertEquals('origin', $info['remote']);
        $this->assertEquals('foo', $info['branch']);
    }

    public function testExtractBranchInfoWithSlashInBranchName()
    {
        $info = Util::extractBranchInfo('refs/source/feature/foo');
        $this->assertEquals('source', $info['remote']);
        $this->assertEquals('feature/foo', $info['branch']);
    }
}
