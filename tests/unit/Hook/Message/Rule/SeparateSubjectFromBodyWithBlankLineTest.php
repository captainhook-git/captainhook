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

class SeparateSubjectFromBodyWithBlankLineTest extends TestCase
{
    public function testPassSuccessOnSubjectOnly(): void
    {
        $msg  = new CommitMessage('Foo bar');
        $rule = new SeparateSubjectFromBodyWithBlankLine();

        $this->assertTrue($rule->pass($msg));
    }

    public function testPassSuccessWithBody(): void
    {
        $msg  = new CommitMessage('Foo bar' . PHP_EOL . PHP_EOL . 'Foo Bar Baz.');
        $rule = new SeparateSubjectFromBodyWithBlankLine();

        $this->assertTrue($rule->pass($msg));
    }

    public function testPassFailNoEmptyLine(): void
    {
        $msg  = new CommitMessage('Foo bar' . PHP_EOL . 'Foo Bar Baz.');
        $rule = new SeparateSubjectFromBodyWithBlankLine();

        $this->assertFalse($rule->pass($msg));
    }
}
