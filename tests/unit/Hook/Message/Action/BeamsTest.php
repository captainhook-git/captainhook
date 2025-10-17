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

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Mockery;
use Exception;
use SebastianFeldmann\Git\CommitMessage;
use PHPUnit\Framework\TestCase;

class BeamsTest extends TestCase
{
    use Mockery;

    public function testConstraint(): void
    {
        $this->assertTrue(Beams::getRestriction()->isApplicableFor('commit-msg'));
        $this->assertFalse(Beams::getRestriction()->isApplicableFor('pre-push'));
    }

    public function testExecute(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Beams::class);
        $repo   = $this->createRepositoryMock();
        $repo->method('getCommitMsg')->willReturn(new CommitMessage('Foo bar baz'));

        $standard = new Beams();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testExecuteImperativeBeginning(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Beams::class, ['checkImperativeBeginningOnly' => true]);
        $repo   = $this->createRepositoryMock();
        $repo->method('getCommitMsg')->willReturn(new CommitMessage('foo added foo bar baz.'));

        $standard = new Beams();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testExecuteFail(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Beams::class);
        $repo   = $this->createRepositoryMock();
        $repo->method('getCommitMsg')->willReturn(new CommitMessage('added foo bar baz.'));

        $standard = new Beams();
        $standard->execute($config, $io, $repo, $action);
    }
}
