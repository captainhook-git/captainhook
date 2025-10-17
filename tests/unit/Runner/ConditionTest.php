<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner;

use CaptainHook\App\Config;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Hook\Condition\Config\CustomValueIsTruthy;
use CaptainHook\App\Hook\Condition\FileChanged\Any;
use CaptainHook\App\Hook\Condition\FileStaged;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use PHPUnit\Framework\TestCase;

class ConditionTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testPHPConditionApply(): void
    {
        $config = $this->createConfigMock();

        $io = $this->createIOMock();
        $io->expects($this->exactly(2))->method('getArgument')->willReturn('');

        $operator   = $this->createGitDiffOperator(['fiz.php', 'baz.php', 'foo.php']);
        $repository = $this->createRepositoryMock('');
        $repository->expects($this->once())->method('getDiffOperator')->willReturn($operator);

        $conditionConfig = new Config\Condition(
            '\\' . Any::class,
            [
                ['foo.php', 'bar.php']
            ]
        );

        $runner = new Condition($io, $repository, $config, 'post-checkout');
        $this->assertTrue($runner->doesConditionApply($conditionConfig));
    }

    public function testConditionNotExecutedDueToConstraint(): void
    {
        $config = $this->createConfigMock();

        $io         = $this->createIOMock();
        $repository = $this->createRepositoryMock('');
        $repository->expects($this->never())->method('getDiffOperator');

        $conditionConfig = new Config\Condition(
            '\\' . Any::class,
            [
                ['foo.php', 'bar.php']
            ]
        );

        $runner = new Condition($io, $repository, $config, 'pre-commit');
        $this->assertTrue($runner->doesConditionApply($conditionConfig));
    }

    public function testClassNotFound(): void
    {
        $this->expectException(Exception::class);

        $conditionConfig = new Config\Condition('\\NotFoundForSure', []);

        $runner = new Condition(
            $this->createIOMock(),
            $this->createRepositoryMock(),
            $this->createConfigMock(),
            'pre-commit'
        );
        $runner->doesConditionApply($conditionConfig);
    }

    public function testConfigDependantCondition(): void
    {
        $config = $this->createConfigMock();
        $config->expects($this->once())->method('getCustomSettings')->willReturn(['FOO' => 'yes']);

        $conditionConfig = new Config\Condition('\\' . CustomValueIsTruthy::class, ['FOO']);

        $runner = new Condition(
            $this->createIOMock(),
            $this->createRepositoryMock(),
            $config,
            'pre-commit'
        );
        $this->assertTrue($runner->doesConditionApply($conditionConfig));
    }

    public function testDoesConditionApplyCli(): void
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('not tested on windows');
        }

        $io         = $this->createIOMock();
        $repository = $this->createRepositoryMock('');

        $conditionConfig = new Config\Condition(CH_PATH_FILES . '/bin/phpunit');

        $runner = new Condition($io, $repository, $this->createConfigMock(), 'pre-commit');
        $this->assertTrue($runner->doesConditionApply($conditionConfig));
    }

    public function testAndConditionIsCorrectlyInterpreted(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(4))->method('getArgument')->willReturn('');

        $operator   = $this->createGitDiffOperator(['fiz.php', 'baz.php', 'foo.php']);
        $repository = $this->createRepositoryMock('');
        $repository->expects($this->exactly(2))->method('getDiffOperator')->willReturn($operator);

        $conditionConfig = new Config\Condition(
            'and',
            [[
                 'exec' => '\\' . Any::class,
                 'args' => [
                     ['foo.php', 'bar.php']
                 ]
             ], [
                 'exec' => '\\' . Any::class,
                 'args' => [
                     ['foo.php', 'bar.php']
                 ]
             ]]
        );

        $runner = new Condition($io, $repository, $this->createConfigMock(), 'post-checkout');
        $this->assertTrue($runner->doesConditionApply($conditionConfig));
    }

    public function testOrConditionIsCorrectlyInterpreted(): void
    {
        $io = $this->createIOMock();
        $io->expects($this->exactly(4))->method('getArgument')->willReturn('');

        $operator   = $this->createGitDiffOperator(['fiz.php', 'baz.php', 'foo.php']);
        $repository = $this->createRepositoryMock('');
        $repository->expects($this->exactly(2))->method('getDiffOperator')->willReturn($operator);

        $conditionConfig = new Config\Condition(
            'or',
            [[
                 'exec' => '\\' . FileStaged\All::class,
                 'args' => [
                     ['foo.php', 'bar.php']
                 ]
             ], [
                 'exec' => '\\' . Any::class,
                 'args' => [
                     ['buz.php', 'bar.php']
                 ]
             ], [
                 'exec' => '\\' . Any::class,
                 'args' => [
                     ['foo.php', 'bar.php']
                 ]
             ]]
        );

        $runner = new Condition($io, $repository, $this->createConfigMock(), 'post-checkout');
        $this->assertTrue($runner->doesConditionApply($conditionConfig));
    }
}
