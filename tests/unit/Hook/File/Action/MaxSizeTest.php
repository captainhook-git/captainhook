<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\File\Action;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Exception\ActionFailed;
use CaptainHook\App\Hooks;
use CaptainHook\App\Mockery;
use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

class MaxSizeTest extends TestCase
{
    use Mockery;

    public function testRestrictions(): void
    {
        $restriction = MaxSize::getRestriction();

        $this->assertTrue($restriction->isApplicableFor(Hooks::PRE_COMMIT));
        $this->assertFalse($restriction->isApplicableFor(Hooks::POST_CHECKOUT));
    }

    public function testPass(): void
    {
        $io       = new NullIO();
        $config   = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo     = $this->createRepositoryMock();
        $files    = [CH_PATH_FILES . '/config/valid.json', CH_PATH_FILES . '/config/valid-with-all-settings.json'];
        $operator = $this->createGitIndexOperator($files);
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($operator);

        $action   = new Config\Action(MaxSize::class, ['maxSize' => '1M']);
        $standard = new MaxSize();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testFail(): void
    {
        $this->expectException(ActionFailed::class);

        $io       = new NullIO();
        $config   = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo     = $this->createRepositoryMock();
        $files    = [CH_PATH_FILES . '/config/empty.json', 'fooBarBaz'];
        $operator = $this->createGitIndexOperator($files);
        $repo->expects($this->once())->method('getIndexOperator')->willReturn($operator);

        $action   = new Config\Action(MaxSize::class, ['maxSize' => '1B']);
        $standard = new MaxSize();
        $standard->execute($config, $io, $repo, $action);
    }

    public function testInvalidSize(): void
    {
        $this->expectException(Exception::class);

        $io       = new NullIO();
        $config   = new Config(CH_PATH_FILES . '/captainhook.json');
        $repo     = $this->createRepositoryMock();
        $files    = [CH_PATH_FILES . '/config/empty.json', 'fooBarBaz'];
        $operator = $this->createGitIndexOperator($files);
        $repo->method('getIndexOperator')->willReturn($operator);

        $action   = new Config\Action(MaxSize::class, ['maxSize' => '1X']);
        $standard = new MaxSize();
        $standard->execute($config, $io, $repo, $action);
    }

    #[DataProvider('toBytesProvider')]
    public function testToBytes(string $input, int $expected): void
    {
        $maxSize = new MaxSize();

        $this->assertSame($expected, $maxSize->toBytes($input));
    }

    public static function toBytesProvider(): array
    {
        return [
            ['512B', 512],
            ['1K', 1024],
            ['5M', 5242880],
            ['2G', 2147483648],
            ['3T', 3298534883328],
            ['4P', 4503599627370496],
        ];
    }

    public function testToBytesThrowsExceptionForInvalidSizeValue(): void
    {
        $maxSize = new MaxSize();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid size value');

        $maxSize->toBytes('123V');
    }
}
