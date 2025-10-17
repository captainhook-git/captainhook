<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Message\RuleBook;

use SebastianFeldmann\Git\CommitMessage;
use PHPUnit\Framework\TestCase;

class RuleSetTest extends TestCase
{
    public function testRuleSetBeams(): void
    {
        $msg   = new CommitMessage('Foo bar baz' . PHP_EOL . PHP_EOL . 'This is a longer body line.');
        $rules = RuleSet::beams();

        $this->assertCount(6, $rules);

        foreach ($rules as $rule) {
            $this->assertTrue($rule->pass($msg));
        }
    }
}
