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
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Mockery as CHMockery;
use SebastianFeldmann\Git\CommitMessage;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Git\Repository;

class PrepareFromFileTest extends TestCase
{
    use ConfigMockery;
    use CHMockery;
    use IOMockery;

    public function testExecutePrepareMessageFromFile(): void
    {
        $dummy = new DummyRepo(['msg.cache' => 'prepared from file']);
        $repo  = new Repository($dummy->getRoot());
        $repo->setCommitMsg(new CommitMessage(''));

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options(['file' => '.git/msg.cache']));

        $standard = new PrepareFromFile();
        $standard->execute($config, $io, $repo, $action);

        $this->assertEquals('prepared from file', $repo->getCommitMsg()->getRawContent());
    }

    public function testExecutePrepareMessageFileDoesNotExist(): void
    {
        $dummy = new DummyRepo();
        $repo  = new Repository($dummy->getRoot());
        $repo->setCommitMsg(new CommitMessage(''));

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options(['file' => '.git/msg.cache']));

        $standard = new PrepareFromFile();
        $standard->execute($config, $io, $repo, $action);

        $this->assertEquals('', $repo->getCommitMsg()->getRawContent());
    }

    public function testExecutePrepareMessageNoFileOptionFailure(): void
    {
        $this->expectException(ActionFailed::class);

        $io      = $this->createIOMock();
        $config  = $this->createConfigMock();
        $repo    = $this->createRepositoryMock();
        $action  = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options([]));

        $standard = new PrepareFromFile();
        $standard->execute($config, $io, $repo, $action);
    }
}
