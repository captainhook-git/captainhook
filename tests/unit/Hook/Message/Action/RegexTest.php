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

class RegexTest extends TestCase
{
    use Mockery;

    public function testConstraint(): void
    {
        $this->assertTrue(Regex::getRestriction()->isApplicableFor('commit-msg'));
        $this->assertFalse(Regex::getRestriction()->isApplicableFor('pre-push'));
    }

    public function testExecuteDefaultSuccess(): void
    {
        $io = $this->createPartialMock(NullIO::class, ['write']);
        $io->expects($this->once())->method('write');
        /** @var NullIO $io */

        $config  = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo    = $this->createRepositoryMock();
        $action  = new Config\Action(Regex::class, ['regex'   => '#bar#']);
        $repo->expects($this->once())->method('getCommitMsg')->willReturn(new CommitMessage('Foo bar baz'));

        $standard = new Regex();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testExecuteCustomSuccess(): void
    {
        $successMessage = 'Regex matched';
        $io             = $this->createPartialMock(NullIO::class, ['write']);
        $io->expects($this->once())->method('write');
        /** @var NullIO $io */

        $config  = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo    = $this->createRepositoryMock();
        $action  = new Config\Action(
            Regex::class,
            [
                'regex'   => '#.*#',
                'success' => $successMessage
            ]
        );
        $repo->expects($this->once())->method('getCommitMsg')->willReturn(new CommitMessage('Foo bar baz'));

        $standard = new Regex();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testExecuteInvalidOption(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo    = $this->createRepositoryMock();
        $action = new Config\Action(Regex::class);

        $standard = new Regex();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testMerging(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo   = $this->createRepositoryMock();
        $repo->expects($this->once())->method('isMerging')->willReturn(true);
        $action = new Config\Action(Regex::class, ['regex'   => '#.*#']);

        $standard = new Regex();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true, 'Since we are merging nothing should happen');
    }

    public function testExecuteNoMatchCustomErrorMessage(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No match for #FooBarBaz#');

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(
            Regex::class,
            [
                'regex' => '#FooBarBaz#',
                'error' => 'No match for %s'
            ]
        );
        $repo   = $this->createRepositoryMock();
        $repo->expects($this->once())->method('getCommitMsg')->willReturn(new CommitMessage('Foo bar baz'));

        $standard = new Regex();
        $standard->execute($config, $io, $repo, $action);
    }
}
