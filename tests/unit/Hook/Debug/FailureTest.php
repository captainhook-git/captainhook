<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Debug;

use CaptainHook\App\Config\Action;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Hook\Debug;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use PHPUnit\Framework\TestCase;

class FailureTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testExecute(): void
    {
        $this->expectException(Exception::class);

        $config       = $this->createConfigMock(true);
        $io           = $this->createIOMock();
        $repository   = $this->createRepositoryMock();
        $action       = new Action('\\' . Debug::class);
        $infoOperator = $this->createGitInfoOperator('1.0.0');

        $io->expects($this->once())->method('getArguments')->willReturn(['foo' => 'bar']);
        $io->expects($this->atLeast(3))->method('write');
        $repository->expects($this->exactly(1))->method('getInfoOperator')->willReturn($infoOperator);

        $debug = new Failure();
        $debug->execute($config, $io, $repository, $action);
    }
}
