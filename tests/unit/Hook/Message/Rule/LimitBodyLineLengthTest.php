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

class LimitBodyLineLengthTest extends TestCase
{
    public function testPassSuccess(): void
    {
        $msg  = new CommitMessage('Foo' . PHP_EOL . PHP_EOL . 'Bar');
        $rule = new LimitBodyLineLength(10);

        $this->assertTrue($rule->pass($msg));
    }

    public function testPassFail(): void
    {
        $msg  = new CommitMessage('Foo' . PHP_EOL . PHP_EOL . 'Bar Baz Fiz Baz');
        $rule = new LimitBodyLineLength(10);

        $this->assertFalse($rule->pass($msg));
    }

    public function testPassFailOnAnyLine(): void
    {
        $msg  = new CommitMessage('Foo' . PHP_EOL . PHP_EOL . 'Fooish' . PHP_EOL . 'Bar Baz Fiz Baz');
        $rule = new LimitBodyLineLength(10);

        $this->assertFalse($rule->pass($msg));
    }
}
