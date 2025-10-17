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

use CaptainHook\App\CH;
use CaptainHook\App\Config;
use CaptainHook\App\Console\IO\NullIO;
use SebastianFeldmann\Git\CommitMessage;
use SebastianFeldmann\Git\Repository;
use PHPUnit\Framework\TestCase;

class PrepareTest extends TestCase
{
    public function testExecutePrepareMessage(): void
    {
        /** @var NullIO $io */
        $io      = $this->createPartialMock(NullIO::class, ['write']);
        $config  = new Config(CH_PATH_FILES . DIRECTORY_SEPARATOR . CH::CONFIG);
        $repo    = $this->createPartialMock(Repository::class, ['isMerging']);
        $action  = new Config\Action(
            Prepare::class,
            [
                'message' => 'Prepared Commit Message'
            ]
        );
        $repo->method('isMerging')->willReturn(false);
        $repo->setCommitMsg(new CommitMessage('Foo bar baz'));

        $standard = new Prepare();
        $standard->execute($config, $io, $repo, $action);

        $this->assertEquals('Prepared Commit Message', $repo->getCommitMsg()->getRawContent());
    }
}
