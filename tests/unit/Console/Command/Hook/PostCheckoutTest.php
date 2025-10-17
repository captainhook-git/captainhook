<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Console\Command\Hook;

use CaptainHook\App\Console\Runtime\Resolver;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Hooks;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use PHPUnit\Framework\TestCase;

class PostCheckoutTest extends TestCase
{
    public function testExecute(): void
    {
        $repo   = new DummyRepo();
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                '--configuration'        => CH_PATH_FILES . '/config/valid.json',
                '--git-directory'        => $repo->getGitDir(),
                Hooks::ARG_PREVIOUS_HEAD => '3e76d8',
                Hooks::ARG_NEW_HEAD      => '7d9ac4',
                Hooks::ARG_MODE          => 'branch'
            ]
        );

        $cmd = new PostCheckout(new Resolver());
        $cmd->run($input, $output);

        $this->assertTrue(true);
    }
}
