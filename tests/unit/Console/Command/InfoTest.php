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
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Runner\Config\Reader;
use Exception;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class InfoTest extends TestCase
{
    public function testFailsWithoutConfig(): void
    {
        $resolver = new Resolver();
        $output   = new NullOutput();
        $input    = new ArrayInput([
            'hook'            => 'pre-commit',
            '--configuration' => 'foo',
        ]);

        $install = new Info($resolver);
        $code    = $install->run($input, $output);

        $this->assertEquals(1, $code);
    }

    public function testDisplaySingleHook(): void
    {
        $dummyRepo = new DummyRepo();
        $resolver  = new Resolver();
        $output    = new NullOutput();
        $input     = new ArrayInput([
            'hook' => 'pre-commit',
            '-c'   => CH_PATH_FILES . '/config/valid.json',
            '-g'   => $dummyRepo->getRoot() . '/.git',
        ]);

        $add = new Info($resolver);
        $this->assertEquals(0, $add->run($input, $output));
    }
}
