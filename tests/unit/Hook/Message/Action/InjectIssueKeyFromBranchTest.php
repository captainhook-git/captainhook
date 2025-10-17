<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Message\Action;

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Config\Options;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Exception\ActionFailed;
use CaptainHook\App\Mockery as CHMockery;
use CaptainHook\App\RepoMock;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Git\CommitMessage;

class InjectIssueKeyFromBranchTest extends TestCase
{
    use ConfigMockery;
    use CHMockery;
    use IOMockery;

    public function testConstraint(): void
    {
        $this->assertTrue(InjectIssueKeyFromBranch::getRestriction()->isApplicableFor('prepare-commit-msg'));
        $this->assertFalse(InjectIssueKeyFromBranch::getRestriction()->isApplicableFor('pre-push'));
    }

    public function testPrependSubject(): void
    {
        $repo = new RepoMock();
        $info = $this->createGitInfoOperator('5.0.0', 'feature/ABCD-12345-foo-bar-baz');

        $repo->setCommitMsg(new CommitMessage('foo' . PHP_EOL . PHP_EOL . 'bar'));
        $repo->setInfoOperator($info);

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options([
            'into' => 'subject',
            'mode' => 'prepend',
        ]));

        $hook = new InjectIssueKeyFromBranch();
        $hook->execute($config, $io, $repo, $action);

        $this->assertEquals('ABCD-12345 foo', $repo->getCommitMsg()->getSubject());
    }

    public function testAppendSubject(): void
    {
        $repo = new RepoMock();
        $info = $this->createGitInfoOperator('5.0.0', 'feature/ABCD-12345-foo-bar-baz');

        $repo->setCommitMsg(new CommitMessage('foo' . PHP_EOL . PHP_EOL . 'bar'));
        $repo->setInfoOperator($info);

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options([
            'into' => 'subject',
            'mode' => 'append',
        ]));

        $hook = new InjectIssueKeyFromBranch();
        $hook->execute($config, $io, $repo, $action);

        $this->assertEquals('foo ABCD-12345', $repo->getCommitMsg()->getSubject());
    }

    public function testAppendBodyWithPrefix(): void
    {
        $repo = new RepoMock();
        $info = $this->createGitInfoOperator('5.0.0', 'feature/ABCD-12345-foo-bar-baz');

        $repo->setCommitMsg(new CommitMessage('foo' . PHP_EOL . PHP_EOL . 'bar'));
        $repo->setInfoOperator($info);

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options([
            'into'   => 'body',
            'mode'   => 'append',
            'prefix' => PHP_EOL . PHP_EOL . 'issue: '
        ]));

        $hook = new InjectIssueKeyFromBranch();
        $hook->execute($config, $io, $repo, $action);

        $this->assertEquals(
            'bar' . PHP_EOL . PHP_EOL . 'issue: ABCD-12345' . PHP_EOL,
            $repo->getCommitMsg()->getBody()
        );
    }

    public function testAppendBodyWithPrefixWithComments(): void
    {
        $repo = new RepoMock();
        $info = $this->createGitInfoOperator('5.0.0', 'feature/ABCD-12345-foo-bar-baz');

        $repo->setCommitMsg(
            new CommitMessage(
                implode(PHP_EOL, ['foo', '', 'bar', '# some comment', '# some comment'])
            )
        );
        $repo->setInfoOperator($info);

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options([
            'into'   => 'body',
            'mode'   => 'append',
            'prefix' => PHP_EOL . PHP_EOL . 'issue: '
        ]));

        $expected = 'bar' . PHP_EOL . PHP_EOL . 'issue: ABCD-12345';
        $hook     = new InjectIssueKeyFromBranch();
        $hook->execute($config, $io, $repo, $action);

        $this->assertEquals(
            $expected,
            $repo->getCommitMsg()->getBody()
        );
        $this->assertStringContainsString('# some comment', $repo->getCommitMsg()->getRawContent());
    }

    public function testIgnoreIssueKeyNotFound(): void
    {
        $repo = new RepoMock();
        $info = $this->createGitInfoOperator('5.0.0', 'foo');

        $repo->setCommitMsg(new CommitMessage('foo' . PHP_EOL . PHP_EOL . 'bar'));
        $repo->setInfoOperator($info);

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options([
            'into'   => 'body',
            'mode'   => 'append',
            'prefix' => PHP_EOL . PHP_EOL . 'issue: '
        ]));

        $hook = new InjectIssueKeyFromBranch();
        $hook->execute($config, $io, $repo, $action);

        $this->assertEquals('bar', $repo->getCommitMsg()->getBody());
    }

    public function testFailIssueKeyNotFound(): void
    {
        $this->expectException(ActionFailed::class);

        $repo = new RepoMock();
        $info = $this->createGitInfoOperator('5.0.0', 'foo');

        $repo->setCommitMsg(new CommitMessage('foo' . PHP_EOL . PHP_EOL . 'bar'));
        $repo->setInfoOperator($info);

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options([
            'force' => true,
        ]));

        $hook = new InjectIssueKeyFromBranch();
        $hook->execute($config, $io, $repo, $action);
    }

    public function testIssueKeyAlreadyInMSG(): void
    {
        $repo = new RepoMock();
        $info = $this->createGitInfoOperator('5.0.0', 'feature/ABCD-12345-foo-bar-baz');

        $repo->setCommitMsg(new CommitMessage('ABCD-12345 foo' . PHP_EOL . PHP_EOL . 'bar'));
        $repo->setInfoOperator($info);

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options([
            'into' => 'subject',
        ]));

        $hook = new InjectIssueKeyFromBranch();
        $hook->execute($config, $io, $repo, $action);

        $this->assertEquals('ABCD-12345 foo', $repo->getCommitMsg()->getSubject());
    }

    public function testSubjectWithPattern(): void
    {
        $repo = new RepoMock();
        $info = $this->createGitInfoOperator('5.0.0', 'feature/ABCD-12345-foo-bar-baz');

        $repo->setCommitMsg(new CommitMessage('foo' . PHP_EOL . PHP_EOL . 'bar'));
        $repo->setInfoOperator($info);

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options([
            'into'    => 'subject',
            'pattern' => '$1: ',
            'mode'    => 'prepend',
        ]));

        $hook = new InjectIssueKeyFromBranch();
        $hook->execute($config, $io, $repo, $action);

        $this->assertEquals('ABCD-12345: foo', $repo->getCommitMsg()->getSubject());
    }

    public function testSubjectWithEmptyPattern(): void
    {
        $repo = new RepoMock();
        $info = $this->createGitInfoOperator('5.0.0', 'feature/ABCD-12345-foo-bar-baz');

        $repo->setCommitMsg(new CommitMessage('foo' . PHP_EOL . PHP_EOL . 'bar'));
        $repo->setInfoOperator($info);

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options([
            'into'    => 'subject',
            'pattern' => '',
            'mode'    => 'prepend',
        ]));

        $hook = new InjectIssueKeyFromBranch();
        $hook->execute($config, $io, $repo, $action);

        $this->assertEquals('ABCD-12345 foo', $repo->getCommitMsg()->getSubject());
    }
}
