<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Console;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ApplicationTest extends TestCase
{
    public function testVersionOutput(): void
    {
        $input = new ArrayInput(['--version' => true]);
        $output = $this->getMockBuilder(NullOutput::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $output->expects($this->once())->method('writeLn');


        $app = new Application('captainhook');
        $app->setAutoExit(false);
        $app->run($input, $output);
    }

    public function testExecuteDefaultHelp(): void
    {
        $input = new ArrayInput(['--help' => true]);
        $output = $this->getMockBuilder(NullOutput::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $app = new Application('captainhook');
        $app->setAutoExit(false);

        $this->assertEquals(0, $app->run($input, $output));
    }

    public function testExecuteList(): void
    {
        $input = new ArrayInput(['command' => 'list']);
        $output = $this->getMockBuilder(NullOutput::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $app = new Application('captainhook');
        $app->setAutoExit(false);

        $this->assertEquals(0, $app->run($input, $output));
    }
}
