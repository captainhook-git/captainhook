<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Diff\Action;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Hook\Debug;
use CaptainHook\App\Mockery as AppMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\Secrets\Regex\Supplier\Aws;
use CaptainHook\Secrets\Regex\Supplier\GitHub;
use CaptainHook\Secrets\Regex\Supplier\Google;
use CaptainHook\Secrets\Regex\Supplier\Password;
use CaptainHook\Secrets\Regex\Supplier\Stripe;
use Exception;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Git\Diff\Change;
use SebastianFeldmann\Git\Diff\File;
use SebastianFeldmann\Git\Diff\Line;

class BlockSecretsTest extends TestCase
{
    use AppMockery;
    use IOMockery;

    public function testConstraint(): void
    {
        $this->assertTrue(BlockSecrets::getRestriction()->isApplicableFor('pre-commit'));
        $this->assertTrue(BlockSecrets::getRestriction()->isApplicableFor('pre-push'));
        $this->assertFalse(BlockSecrets::getRestriction()->isApplicableFor('post-merge'));
    }

    public function testExecuteSuccess(): void
    {
        $diffOperator = $this->createGitDiffOperator();
        $diffOperator->method('compareIndexTo')->willReturn(
            $this->createChanges('fail.txt', ['foo', 'bar', 'baz'])
        );

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(BlockSecrets::class, ['entropyThreshold' => 10.0]);
        $repo   = $this->createRepositoryMock();
        $repo->method('getDiffOperator')->willReturn($diffOperator);

        $standard = new BlockSecrets();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testExecuteSuccessOnPush(): void
    {
        $diffOperator = $this->createGitDiffOperator();
        $diffOperator->method('compareIndexTo')->willReturn(
            $this->createChanges('fail.txt', ['foo', 'bar', 'baz'])
        );

        $io = $this->createIOMock();
        $io->method('getArgument')->willReturn('hook:pre-push');
        $io->method('getStandardInput')->willReturn(['main 12345 main 98765']);
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(BlockSecrets::class, ['entropyThreshold' => 10.0]);
        $repo   = $this->createRepositoryMock();
        $repo->method('getDiffOperator')->willReturn($diffOperator);

        $standard = new BlockSecrets();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testExecuteSuccessWithEntropyCheck(): void
    {
        $diffOperator = $this->createGitDiffOperator();
        $diffOperator->method('compareIndexTo')->willReturn(
            $this->createChanges('fail.php', ['foo', 'bar', 'baz'])
        );

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(BlockSecrets::class, ['entropyThreshold' => 10.0]);
        $repo   = $this->createRepositoryMock();
        $repo->method('getDiffOperator')->willReturn($diffOperator);

        $standard = new BlockSecrets();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    public function testExecuteFailure(): void
    {
        $this->expectException(Exception::class);

        $options = [
            'suppliers' => [
                Aws::class,
                Password::class,
                Google::class,
                GitHub::class,
                Stripe::class
            ]
        ];

        $diffOperator = $this->createGitDiffOperator();
        $diffOperator->method('compareIndexTo')->willReturn(
            $this->createChanges('fail.txt', ['foo', 'AKIAIOSFODNN7EXAMPLE', 'bar'])
        );

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(BlockSecrets::class, $options);
        $repo   = $this->createRepositoryMock();
        $repo->method('getDiffOperator')->willReturn($diffOperator);

        $standard = new BlockSecrets();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testExecuteFailureByEntropy(): void
    {
        $this->expectException(Exception::class);

        $options = ['entropyThreshold' => 1];

        $diffOperator = $this->createGitDiffOperator();
        $diffOperator->method('compareIndexTo')->willReturn(
            $this->createChanges('fail.php', ['foo', '$password = "5ad7$-9Op0-x2§d"', 'bar'])
        );

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(BlockSecrets::class, $options);
        $repo   = $this->createRepositoryMock();
        $repo->method('getDiffOperator')->willReturn($diffOperator);

        $standard = new BlockSecrets();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testExecuteFailedByEntropyButAllowed(): void
    {
        $options = ['entropyThreshold' => 1, 'allowed' => ['#5ad7\\$\\-9Op0\\-x2§d#']];

        $diffOperator = $this->createGitDiffOperator();
        $diffOperator->expects($this->once())->method('compareIndexTo')->willReturn(
            $this->createChanges('fail.php', ['foo', '$password = "5ad7$-9Op0-x2§d"', 'bar'])
        );

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(BlockSecrets::class, $options);
        $repo   = $this->createRepositoryMock();
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($diffOperator);

        $standard = new BlockSecrets();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testExecuteFailedByEntropyBruteForce(): void
    {
        $this->expectException(Exception::class);

        $options = ['entropyThreshold' => 3.6];

        $diffOperator = $this->createGitDiffOperator();
        $diffOperator->expects($this->once())->method('compareIndexTo')->willReturn(
            $this->createChanges('fail.txt', ['foo', 'SuperDuperVeryVeryLongWord', 'password = 5ad7$_9Op0-x2§d', 'bar'])
        );

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(BlockSecrets::class, $options);
        $repo   = $this->createRepositoryMock();
        $repo->expects($this->once())->method('getDiffOperator')->willReturn($diffOperator);

        $standard = new BlockSecrets();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testExecuteProviderNotFound(): void
    {
        $this->expectException(Exception::class);

        $options = [
            'suppliers' => [
                'Fooooooooooooo'
            ]
        ];

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(BlockSecrets::class, $options);
        $repo   = $this->createRepositoryMock();
        $repo->method('getDiffOperator')->willReturn($this->createGitDiffOperator());

        $standard = new BlockSecrets();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testExecuteInvalidProvider(): void
    {
        $this->expectException(Exception::class);

        $options = [
            'suppliers' => [
                Debug\Failure::class
            ]
        ];

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(BlockSecrets::class, $options);
        $repo   = $this->createRepositoryMock();
        $repo->method('getDiffOperator')->willReturn($this->createGitDiffOperator());

        $standard = new BlockSecrets();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testExecuteAllow(): void
    {
        $diffOperator = $this->createGitDiffOperator();
        $diffOperator->method('compareIndexTo')->willReturn(
            $this->createChanges('fail.txt', ['foo', 'bar'])
        );

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $action = new Config\Action(BlockSecrets::class, [
            'blocked' => ['#f[a-z]+#'],
            'allowed' => ['#foo#']
        ]);
        $repo = $this->createRepositoryMock();
        $repo->expects($this->atLeast(1))->method('getDiffOperator')->willReturn($diffOperator);

        $standard = new BlockSecrets();
        $standard->execute($config, $io, $repo, $action);
    }

    /**
     * @param string        $fileName
     * @param array<string> $lines
     * @return array<File>
     */
    private function createChanges(string $fileName, array $lines): array
    {
        $diffChange = new Change('+123,456 -789,012', '');
        foreach ($lines as $line) {
            $diffChange->addLine(new Line('added', $line));
        }
        $diffFile = new File($fileName, 'new');
        $diffFile->addChange($diffChange);

        return [$diffFile];
    }
}
