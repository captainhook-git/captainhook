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

class CapitalizeSubjectTest extends TestCase
{
    public function testPassSuccess(): void
    {
        $msg  = new CommitMessage('Foo');
        $rule = new CapitalizeSubject();

        $this->assertTrue($rule->pass($msg));
    }

    public function testPassFail(): void
    {
        $msg  = new CommitMessage('foo');
        $rule = new CapitalizeSubject();

        $this->assertFalse($rule->pass($msg));
    }

    public function testPassFailOnEmptyMessage(): void
    {
        $msg  = new CommitMessage('');
        $rule = new CapitalizeSubject();

        $this->assertFalse($rule->pass($msg));
    }
}
