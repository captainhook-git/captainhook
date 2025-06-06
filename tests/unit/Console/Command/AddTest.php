<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Console\Command;

use CaptainHook\App\Console\IO\DefaultIO;
use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Console\Runtime\Resolver;
use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

class AddTest extends TestCase
{
    /**
     * Tests Add::run
     *
     * @throws \Exception
     */
    public function testExecuteNoConfig(): void
    {
        $resolver = new Resolver();
        $input    = new ArrayInput(
            [
                'hook' => 'pre-commit',
                '--configuration' => 'foo'
            ]
        );

        $output  = new NullOutput();
        $install = new Add($resolver);
        $install->setIO(new NullIO());
        $code = $install->run($input, $output);

        $this->assertEquals(1, $code);
    }

    /**
     * Tests Add::run
     */
    public function testExecutePreCommit(): void
    {
        $resolver = new Resolver();
        $config   = sys_get_temp_dir() . '/captainhook-add.json';
        copy(CH_PATH_FILES . '/config/valid.json', $config);


        $add    = new Add($resolver);
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => 'pre-commit',
                '--configuration' => $config
            ]
        );

        $io = $this->getMockBuilder(DefaultIO::class)
                   ->disableOriginalConstructor()
                   ->getMock();

        $io->method('ask')->will($this->onConsecutiveCalls('\\Foo\\Bar', 'n'));
        $io->expects($this->once())->method('write');

        $add->setIO($io);
        $add->run($input, $output);

        $json = json_decode(file_get_contents($config), true);
        $this->assertCount(2, $json['pre-commit']['actions']);

        unlink($config);
    }
}
