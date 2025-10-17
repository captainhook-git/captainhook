<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Message;

use CaptainHook\App\Hook\Message\Rule\CapitalizeSubject;
use CaptainHook\App\Hook\Message\Rule\MsgNotEmpty;
use CaptainHook\App\Hook\Message\Rule\UseImperativeMood;
use SebastianFeldmann\Git\CommitMessage;
use PHPUnit\Framework\TestCase;

class RuleBookTest extends TestCase
{
    public function testValidOnEmptyRuleList(): void
    {
        $msg = new CommitMessage('Foo');
        $v   = new RuleBook();

        $v->validate($msg);
        $this->assertTrue(true);
    }

    public function testSetRulesValid(): void
    {
        $msg = new CommitMessage('Foo');
        $v   = new RuleBook();
        $v->setRules([new MsgNotEmpty()]);

        $problems = $v->validate($msg);
        $this->assertEquals([], $problems);
    }

    public function testSetRulesInvalid(): void
    {
        $msg = new CommitMessage('');
        $v   = new RuleBook();
        $v->setRules([new MsgNotEmpty()]);
        $problems = $v->validate($msg);
        $this->assertCount(1, $problems);
    }

    public function testAddRuleInvalid(): void
    {
        $msg = new CommitMessage('foo bar baz');
        $v   = new RuleBook();
        $v->setRules([new MsgNotEmpty()]);
        $v->addRule(new CapitalizeSubject());

        $problems = $v->validate($msg);
        $this->assertCount(1, $problems);
    }

    public function testAddRuleInvalidMultiLineProblem(): void
    {
        $msg = new CommitMessage('fixed bar baz');
        $v   = new RuleBook();
        $v->setRules([new UseImperativeMood()]);

        $problems = $v->validate($msg);
        $this->assertCount(1, $problems);
    }
}
