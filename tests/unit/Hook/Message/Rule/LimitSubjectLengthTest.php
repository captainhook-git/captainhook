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

class LimitSubjectLengthTest extends TestCase
{
    public function testPassSuccess(): void
    {
        $msg  = new CommitMessage('Foo Bar');
        $rule = new LimitSubjectLength(10);

        $this->assertTrue($rule->pass($msg));
    }

    public function testPassFail(): void
    {
        $msg  = new CommitMessage('Foo Bar Baz Fiz Baz');
        $rule = new LimitSubjectLength(10);

        $this->assertFalse($rule->pass($msg));
    }
}
