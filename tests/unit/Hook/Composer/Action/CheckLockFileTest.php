<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Composer\Action;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Mockery;
use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CheckLockFileTest extends TestCase
{
    use Mockery;

    public function testExecute(): void
    {
        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo   = $this->createRepositoryMock();
        $action = new Config\Action(
            CheckLockFile::class,
            ['path' => CH_PATH_FILES . '/composer/valid', 'name' => 'composer.fake']
        );
        $standard = new CheckLockFile();
        $standard->execute($config, $io, $repo, $action);

        $this->assertTrue(true);
    }

    #[DataProvider('policyConfigurations')]
    public function testExecuteWithPolicy(
        bool $withPlatform,
        bool $changedPolicy,
        string $lockHash,
        bool $outdated
    ): void {
        $composerConfig = ['policy' => ['advisories' => ['block' => $changedPolicy]]];
        if ($withPlatform) {
            $composerConfig['platform'] = ['php' => '8.0.0'];
        }
        $directory = vfsStream::setup('composer', null, [
            'composer.json' => json_encode(['config' => $composerConfig]),
            'composer.lock' => json_encode(['content-hash' => $lockHash]),
        ]);
        $action = new Config\Action(CheckLockFile::class, ['path' => $directory->url()]);

        if ($outdated) {
            $this->expectExceptionMessage('Your composer.lock file is out of date');
            $this->expectException(Exception::class);
        }

        $standard = new CheckLockFile();
        $standard->execute(
            new Config(CH_PATH_FILES . '/captainhook.json'),
            new NullIO(),
            $this->createRepositoryMock(),
            $action
        );

        $this->assertTrue(true);
    }

    /**
     * @return array<string, array{bool, bool, string, bool}>
     */
    public static function policyConfigurations(): array
    {
        return [
            'policy only' => [false, false, '9c8aef63892fcbcf3f78ce06dca9c8e4', false],
            'policy and platform' => [true, false, 'e5d31e87b36c3dd2f424a4585c07a01e', false],
            'changed policy only' => [false, true, '9c8aef63892fcbcf3f78ce06dca9c8e4', true],
            'changed policy with platform' => [true, true, 'e5d31e87b36c3dd2f424a4585c07a01e', true],
            'legacy policy only' => [false, false, 'd751713988987e9331980363e24189ce', false],
            'legacy policy and platform' => [true, false, 'e1263a071d7944e34ef234afdc44ba57', false],
            'legacy changed policy only' => [false, true, 'd751713988987e9331980363e24189ce', false],
            'legacy changed policy with platform' => [true, true, 'e1263a071d7944e34ef234afdc44ba57', false],
            'added platform' => [true, false, '9c8aef63892fcbcf3f78ce06dca9c8e4', true],
            'removed platform' => [false, false, 'e5d31e87b36c3dd2f424a4585c07a01e', true],
            'legacy added platform' => [true, false, 'd751713988987e9331980363e24189ce', true],
            'legacy removed platform' => [false, false, 'e1263a071d7944e34ef234afdc44ba57', true],
        ];
    }

    public function testExecuteFail(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo   = $this->createRepositoryMock();
        $action = new Config\Action(
            CheckLockFile::class,
            ['path' => CH_PATH_FILES . '/composer/invalid-hash', 'name' => 'composer.fake']
        );

        $standard = new CheckLockFile();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testExecuteNoHash(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo   = $this->createRepositoryMock();
        $action = new Config\Action(
            CheckLockFile::class,
            ['path' => CH_PATH_FILES . '/composer/no-hash', 'name' => 'composer.fake']
        );

        $standard = new CheckLockFile();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testExecuteInvalidPath(): void
    {
        $this->expectException(Exception::class);

        $io     = new NullIO();
        $config = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo   = $this->createRepositoryMock();
        $action = new Config\Action(
            CheckLockFile::class,
            ['path' => CH_PATH_FILES . '/composer/not-there']
        );
        $standard = new CheckLockFile();
        $standard->execute($config, $io, $repo, $action);
    }
}
