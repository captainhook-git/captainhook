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

use CaptainHook\App\Console\Runtime\Resolver;
use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class EnableTest extends TestCase
{
    /**
     * Tests Enable::run
     *
     * @throws \Exception
     */
    public function testExecuteNoConfig(): void
    {
        $resolver = new Resolver();
        $output   = new NullOutput();
        $input    = new ArrayInput([
            'hook'            => 'pre-commit',
            '--configuration' => 'foo',
        ]);

        $install = new Enable($resolver);
        $code    = $install->run($input, $output);

        $this->assertEquals(1, $code);
    }

    /**
     * Tests Enable::run
     *
     * @throws \Exception
     */
    public function testExecuteEnablePrePush(): void
    {
        $resolver   = new Resolver();
        $output     = new NullOutput();
        $fakeConfig = vfsStream::setup(
            'root',
            null,
            ['captainhook.json' => file_get_contents(CH_PATH_FILES . '/config/valid.json')]
        );
        $input      = new ArrayInput([
            'hook'            => 'pre-push',
            '--configuration' => $fakeConfig->url() . '/captainhook.json',
        ]);

        $add = new Enable($resolver);
        $add->run($input, $output);

        $json = json_decode($fakeConfig->getChild('captainhook.json')->getContent(), true);
        $this->assertTrue($json['pre-push']['enabled']);
    }
}
