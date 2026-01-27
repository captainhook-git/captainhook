<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\UserInput;

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Config\Options;
use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Hook\Message\Action\CacheOnFail;
use CaptainHook\App\Hook\Message\EventHandler\WriteCacheFile;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use PHPUnit\Framework\TestCase;

class AskConfirmationTest extends TestCase
{
    use ConfigMockery;
    use CHMockery;

    public function testExecute(): void
    {
        $io     = new NullIO();
        $config = $this->createConfigMock();
        $action = $this->createActionConfigMock();
        $action->method('getOptions')->willReturn(new Options(['message' => 'what?']));
        $repo   = $this->createRepositoryMock();

        $exe = new AskConfirmation();
        $exe->execute($config, $io, $repo, $action);

        $handlers = AskConfirmation::getEventHandlers($action);
        $this->assertNotEmpty($handlers);
        $this->assertInstanceOf(EventHandler\AskConfirmation::class, $handlers['onHookSuccess'][0]);
    }
}
