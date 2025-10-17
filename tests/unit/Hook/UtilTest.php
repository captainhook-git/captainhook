<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use CaptainHook\App\Console\IO\Mockery;

class UtilTest extends TestCase
{
    use Mockery;

    public function testIsValid(): void
    {
        $this->assertTrue(Util::isValid('pre-commit'));
        $this->assertTrue(Util::isValid('pre-push'));
        $this->assertTrue(Util::isValid('commit-msg'));
        $this->assertFalse(Util::isValid('foo'));
    }

    public function testIsInstallable(): void
    {
        $this->assertTrue(Util::isInstallable('pre-commit'));
        $this->assertTrue(Util::isInstallable('pre-push'));
        $this->assertTrue(Util::isValid('post-change'));
        $this->assertFalse(Util::isInstallable('post-change'));
    }

    public function testGetValidHooks(): void
    {
        $this->assertArrayHasKey('pre-commit', Util::getValidHooks());
        $this->assertArrayHasKey('pre-push', Util::getValidHooks());
        $this->assertArrayHasKey('commit-msg', Util::getValidHooks());
    }

    #[DataProvider('providerValidCommands')]
    public function testGetHookCommandValid(string $class, string $hook): void
    {
        $this->assertEquals($class, Util::getHookCommand($hook));
        $this->assertEquals('PreCommit', Util::getHookCommand('pre-commit'));
        $this->assertEquals('PrepareCommitMsg', Util::getHookCommand('prepare-commit-msg'));
        $this->assertEquals('PrePush', Util::getHookCommand('pre-push'));
    }

    public static function providerValidCommands(): array
    {
        return [
            ['CommitMsg', 'commit-msg'],
            ['PreCommit', 'pre-commit'],
            ['PrepareCommitMsg', 'prepare-commit-msg'],
            ['PrePush', 'pre-push'],
        ];
    }

    #[DataProvider('providerInvalidCommands')]
    public function testGetHookCommandInvalid(string $hook): void
    {
        $this->expectException(RuntimeException::class);

        $this->assertEquals('', Util::getHookCommand($hook));
    }

    public static function providerInvalidCommands(): array
    {
        return [
            [''],
            ['foo'],
        ];
    }

    public function testGetHooks(): void
    {
        $this->assertContains('pre-commit', Util::getHooks());
        $this->assertContains('pre-push', Util::getHooks());
        $this->assertContains('commit-msg', Util::getHooks());
    }

    public function testFindPreviousHeadFallback(): void
    {
        $io = $this->createIOMock();
        $io->method('getStandardInput')->willReturn([]);
        $io->method('getArgument')->willReturn('HEAD@{1}');

        $prev = Util::findPreviousHead($io);

        $this->assertEquals('HEAD@{1}', $prev);
    }

    public function testFindPreviousHeadFromStdIn(): void
    {
        $io = $this->createIOMock();
        $io->method('getStandardInput')->willReturn(['foo a1a1a1a1 Something', 'bar b2b2b2b2 Something else']);

        $prev = Util::findPreviousHead($io);

        $this->assertEquals('a1a1a1a1^', $prev);
    }
}
