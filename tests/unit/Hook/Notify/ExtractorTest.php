<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Notify;

use PHPUnit\Framework\TestCase;

class ExtractorTest extends TestCase
{
    public function testExtractMultilineNotification()
    {
        $message = 'Foo' . PHP_EOL . 'git-notify: FIZ' . PHP_EOL . 'baz' . PHP_EOL . 'biz';

        $notification = Extractor::extractNotification($message);
        $this->assertEquals(3, $notification->length());
    }

    public function testExtractNotificationWithoutPrefix()
    {
        $message = 'Foo' . PHP_EOL . 'bar';

        $notification = Extractor::extractNotification($message);
        $this->assertEquals(0, $notification->length());
    }
}
