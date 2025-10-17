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

use CaptainHook\App\Console\IO\NullIO;
use CaptainHook\App\Console\Runtime\Resolver;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Hook\Template;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class UninstallTest extends TestCase
{
    public function testFailMissingConfig(): void
    {
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => 'pre-commit',
                '--configuration' => 'foo',
                '--git-directory' => 'bar'
            ]
        );

        $install = new Uninstall(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $code    = $install->run($input, $output);

        $this->assertEquals(1, $code);
    }

    public function testUninstallPreCommitHook(): void
    {
        $repo   = new DummyRepo([
            'config' => '# fake git config',
            'hooks'  => [
                'pre-commit' => '# fake pre-commit hook file',
                'pre-push'   => '# fake pre-push hook file',
            ]
        ]);
        $output = new NullOutput();
        $input  = new ArrayInput(
            [
                'hook'            => 'pre-commit',
                '--git-directory' => $repo->getGitDir(),
                '--configuration' => CH_PATH_FILES . '/config/valid.json'
            ]
        );

        $install = new Uninstall(new Resolver(CH_PATH_FILES . '/bin/captainhook'));
        $install->run($input, $output);

        $this->assertFalse($repo->hookExists('pre-commit'));
    }
}
