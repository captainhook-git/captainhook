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
use CaptainHook\App\Hook\Message\Rule\CapitalizeSubject;
use CaptainHook\App\Hook\Message\Rule\LimitSubjectLength;
use CaptainHook\App\Mockery;
use Exception;
use SebastianFeldmann\Git\CommitMessage;
use PHPUnit\Framework\TestCase;

class RulesTest extends TestCase
{
    use Mockery;

    public function testExecuteEmptyRules(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Rules::class);
        $repo   = $this->createRepositoryMock();
        $repo->method('getCommitMsg')->willReturn(new CommitMessage('Foo bar baz'));

        $standard = new Rules();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testNoValidationOnMerging(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Rules::class);
        $repo   = $this->createRepositoryMock();
        $repo->expects($this->once())->method('isMerging')->willReturn(true);

        $standard = new Rules();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testExecuteClassNotFound(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo   = $this->createRepositoryMock();
        $action = new Config\Action(Rules::class, [Foo::class]);

        $standard = new Rules();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testExecuteInvalidClass(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Rules::class, [Validator::class]);
        $repo   = $this->createRepositoryMock();

        $standard = new Rules();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testExecuteValidRule(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Rules::class, [CapitalizeSubject::class]);
        $repo   = $this->createRepositoryMock();
        $repo->method('getCommitMsg')->willReturn(new CommitMessage('Foo bar baz'));

        $standard = new Rules();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testExecuteValidRuleWithArguments(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Rules::class, [[LimitSubjectLength::class, [10]]]);
        $repo   = $this->createRepositoryMock();
        $repo->method('getCommitMsg')->willReturn(new CommitMessage('Foo bar baz'));

        try {
            $standard = new Rules();
            $standard->execute($config, $io, $repo, $action);

            // exception should be thrown before this
            $this->assterTrue(false);
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testExecuteLimitSubjectLengthRuleWithUnicode(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Rules::class, [[LimitSubjectLength::class, [10]]]);
        $repo   = $this->createRepositoryMock();
        $repo->method('getCommitMsg')->willReturn(new CommitMessage('Föö bäü¿'));

        try {
            $standard = new Rules();
            $standard->execute($config, $io, $repo, $action);

            // no exception should be thrown
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testNoRule(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Rules::class, [NoRule::class]);
        $repo   = $this->createRepositoryMock();
        $repo->method('getCommitMsg')->willReturn(new CommitMessage('Foo bar baz'));

        $standard = new Rules();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testInvalidComplexRule(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(Rules::class, [[LimitSubjectLength::class, 'foo']]);
        $repo   = $this->createRepositoryMock();

        $standard = new Rules();
        $standard->execute($config, $io, $repo, $action);
    }
}
