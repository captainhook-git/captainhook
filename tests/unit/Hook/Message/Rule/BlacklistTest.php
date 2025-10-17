<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Message\Rule;

use SebastianFeldmann\Git\CommitMessage;
use PHPUnit\Framework\TestCase;

class BlacklistTest extends TestCase
{
    public function testCaseInsensitiveHit(): void
    {
        $msg  = new CommitMessage('Foo bar baz' . PHP_EOL . PHP_EOL . 'Some Body text that is longer');
        $list = new Blacklist(false);
        $list->setBodyBlacklist(['body']);
        $list->setSubjectBlacklist(['Fiz']);

        $this->assertFalse($list->pass($msg));
    }

    public function testCaseSensitiveMiss(): void
    {
        $msg  = new CommitMessage('Foo bar baz' . PHP_EOL . PHP_EOL . 'Some Body text that is longer');
        $list = new Blacklist(true);
        $list->setBodyBlacklist(['body']);
        $list->setSubjectBlacklist(['Fiz']);

        $this->assertTrue($list->pass($msg));
    }
}
