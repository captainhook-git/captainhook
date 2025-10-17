<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Condition\Config;

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Hook\Condition\FileChanged\Any;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use PHPUnit\Framework\TestCase;

class CustomValueIsFalsyTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testCustomValueIsTruthyIsTrue(): void
    {
        $io         = $this->createIOMock();
        $repository = $this->createRepositoryMock('');
        $config     = $this->createConfigMock();
        $config->method('getCustomSettings')->willReturn(['FOOO' => 'no', 'BARR' => 1]);

        $condition = new CustomValueIsFalsy('FOOO');
        $condition->setConfig($config);
        $this->assertTrue($condition->isTrue($io, $repository));

        $condition = new CustomValueIsFalsy('BARR');
        $condition->setConfig($config);
        $this->assertFalse($condition->isTrue($io, $repository));

        $condition = new CustomValueIsFalsy('BAZZZ');
        $condition->setConfig($config);
        $this->assertTrue($condition->isTrue($io, $repository));
    }

    public function testIsTrueFailure(): void
    {
        $this->expectException(Exception::class);

        $io         = $this->createIOMock();
        $repository = $this->createRepositoryMock('');

        $condition = new CustomValueIsFalsy('FOOO');
        $this->assertTrue($condition->isTrue($io, $repository));
    }
}
