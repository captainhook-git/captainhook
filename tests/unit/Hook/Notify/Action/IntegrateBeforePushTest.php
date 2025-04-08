<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Notify\Action;

use CaptainHook\App\Config\Action;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Hooks;
use CaptainHook\App\Mockery as AppMockery;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Git\Log\Commit;

/**
 * Class IntegrateBeforePushTest
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.19.1
 */
class IntegrateBeforePushTest extends TestCase
{
    use AppMockery;
    use ConfigMockery;
    use IOMockery;

    /**
     * Tests IntegrateBeforePush::getRestriction
     */
    public function testRestrictions(): void
    {
        $restriction = IntegrateBeforePush::getRestriction();

        $this->assertTrue($restriction->isApplicableFor(Hooks::PRE_PUSH));
        $this->assertFalse($restriction->isApplicableFor(Hooks::PRE_COMMIT));
    }

    /**
     * Tests: IntegrateBeforePush::execute
     */
    public function testBlockPush(): void
    {
        $io           = $this->createIOMock();
        $config       = $this->createConfigMock();
        $actionConfig = new Action(
            'IntegrateBeforePush',
            [
                'trigger' => '[integrate]',
                'branch'  => 'origin/main',
            ]
        );

        $repository = $this->createRepositoryMock();
        $remote     = $this->createGitRemoteOperator();
        $log        = $this->createGitLogOperator();
        $logs       = [
            new Commit('12345', [], 'foo msg', '', new DateTimeImmutable(), 'sf'),
            new Commit('67890', [], 'bar msg', '', new DateTimeImmutable(), 'sf'),
            new Commit('45678', [], '[integrate] config update', '', new DateTimeImmutable(), 'sf'),
        ];

        $remote->expects($this->once())->method('fetchBranch');
        $log->expects($this->once())->method('getCommitsBetween')->willReturn($logs);
        $repository->expects($this->once())->method('getLogOperator')->willReturn($log);
        $repository->expects($this->once())->method('getRemoteOperator')->willReturn($remote);

        $this->expectException(Exception::class);

        $action = new IntegrateBeforePush();
        $action->execute($config, $io, $repository, $actionConfig);
    }

    /**
     * Tests: IntegrateBeforePush::execute
     */
    public function testNoTrigger(): void
    {
        $io           = $this->createIOMock();
        $config       = $this->createConfigMock();
        $actionConfig = new Action(
            'IntegrateBeforePush',
            [
                'trigger' => '[integrate]',
                'branch'  => 'origin/main',
            ]
        );

        $repository = $this->createRepositoryMock();
        $remote     = $this->createGitRemoteOperator();
        $log        = $this->createGitLogOperator();
        $logs       = [
            new Commit('12345', [], 'foo msg', '', new DateTimeImmutable(), 'sf'),
            new Commit('67890', [], 'bar msg', '', new DateTimeImmutable(), 'sf'),
            new Commit('45678', [], 'baz msg', '', new DateTimeImmutable(), 'sf'),
        ];

        $remote->expects($this->once())->method('fetchBranch');
        $log->expects($this->once())->method('getCommitsBetween')->willReturn($logs);
        $repository->expects($this->once())->method('getLogOperator')->willReturn($log);
        $repository->expects($this->once())->method('getRemoteOperator')->willReturn($remote);

        $action = new IntegrateBeforePush();
        $action->execute($config, $io, $repository, $actionConfig);
    }
}
