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

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Config\Options;
use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Hook\Message\EventHandler\WriteCacheFile;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use PHPUnit\Framework\TestCase;

class CacheOnFailTest extends TestCase
{
    use ConfigMockery;
    use CHMockery;

    public function testExecute(): void
    {
        $io     = new NullIO();
        $config = $this->createConfigMock();
        $action = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options(['file' => 'foo']));
        $repo   = $this->createRepositoryMock();

        $exe = new CacheOnFail();
        $exe->execute($config, $io, $repo, $action);

        $handlers = CacheOnFail::getEventHandlers($action);
        $this->assertFalse(empty($handlers));
        $this->assertInstanceOf(WriteCacheFile::class, $handlers['onHookFailure'][0]);
    }

    public function testExecuteFail(): void
    {
        $this->expectException(Exception::class);

        $action = $this->createActionConfigMock();

        CacheOnFail::getEventHandlers($action);
    }
}
