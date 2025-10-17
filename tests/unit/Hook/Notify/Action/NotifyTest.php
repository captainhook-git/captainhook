<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Notify\Action;

use CaptainHook\App\Config\Options;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Mockery as AppMockery;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use DateTimeImmutable;
use SebastianFeldmann\Git\Log\Commit;
use PHPUnit\Framework\TestCase;

class NotifyTest extends TestCase
{
    use IOMockery;
    use AppMockery;
    use ConfigMockery;

    public function testConstraint(): void
    {
        $this->assertTrue(Notify::getRestriction()->isApplicableFor('post-checkout'));
        $this->assertFalse(Notify::getRestriction()->isApplicableFor('commit-msg'));
    }

    public function testExecuteWithNotifications()
    {
        $io      = $this->createIOMock();
        $repo    = $this->createRepositoryMock();
        $logOp   = $this->createGitLogOperator();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $options = new Options([]);

        $fakeCommits = [
            new Commit('a1a1a1', [], 'Subject A', 'Some Body', new DateTimeImmutable('now'), 'Sebastian'),
            new Commit('b2b2b2', [], 'Subject B', 'git-notify: WARNING', new DateTimeImmutable('now'), 'Sebastian')
        ];

        $io->expects($this->atLeastOnce())->method('write');
        $repo->method('getLogOperator')->willReturn($logOp);
        $logOp->method('getCommitsBetween')->willReturn($fakeCommits);
        $action->method('getOptions')->willReturn($options);

        $notify = new Notify();
        $notify->execute($config, $io, $repo, $action);
    }

    public function testExecuteWithoutNotifications()
    {
        $io      = $this->createIOMock();
        $repo    = $this->createRepositoryMock();
        $logOp   = $this->createGitLogOperator();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $options = new Options([]);

        $fakeCommits = [
            new Commit('a1a1a1', [], 'Subject A', 'Body A', new DateTimeImmutable('now'), 'Sebastian'),
            new Commit('b2b2b2', [], 'Subject B', 'Body B', new DateTimeImmutable('now'), 'Sebastian')
        ];

        $io->expects($this->never())->method('write');
        $repo->method('getLogOperator')->willReturn($logOp);
        $logOp->method('getCommitsBetween')->willReturn($fakeCommits);
        $action->method('getOptions')->willReturn($options);

        $notify = new Notify();
        $notify->execute($config, $io, $repo, $action);
    }

    public function testExecuteWithCustomPrefix()
    {
        $io      = $this->createIOMock();
        $repo    = $this->createRepositoryMock();
        $logOp   = $this->createGitLogOperator();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $options = new Options(['prefix' => 'git-alert:']);

        $fakeCommits = [
            new Commit('a1a1a1', [], 'Subject A', 'Some Body', new DateTimeImmutable('now'), 'Sebastian'),
            new Commit('b2b2b2', [], 'Subject B', 'git-alert: WARNING', new DateTimeImmutable('now'), 'Sebastian')
        ];

        $io->expects($this->atLeastOnce())->method('write');
        $repo->method('getLogOperator')->willReturn($logOp);
        $logOp->method('getCommitsBetween')->willReturn($fakeCommits);
        $action->method('getOptions')->willReturn($options);

        $notify = new Notify();
        $notify->execute($config, $io, $repo, $action);
    }
}
