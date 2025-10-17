<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Git\ChangedFiles\Detector;

use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Git\Range\Detector\Fallback;
use PHPUnit\Framework\TestCase;

/**
 * Class FallbackTest
 *
 * Tests the Fallback detector, since currently there is no (test) use case were it is used
 * there has to be this dedicated testcase.
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.20.0
 */
class FallbackTest extends TestCase
{
    use IOMockery;

    public function testGetRanges()
    {
        $io = $this->createIOMock();
        $io->method('getArgument')->willReturn('HEAD@{1}');

        $fallback = new Fallback();
        $ranges   = $fallback->getRanges($io);

        $this->assertCount(1, $ranges);
        $this->assertEquals('HEAD@{1}', $ranges[0]->from()->id());
        $this->assertEquals('HEAD', $ranges[0]->to()->id());
    }
}
