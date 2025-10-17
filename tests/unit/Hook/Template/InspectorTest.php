<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Template;

use CaptainHook\App\CH;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Git\DummyRepo;
use CaptainHook\App\Mockery as AppMockery;
use Exception;
use PHPUnit\Framework\TestCase;

class InspectorTest extends TestCase
{
    use IOMockery;
    use AppMockery;

    public function testInspectValid(): void
    {
        $io    = $this->createIOMock();
        $dummy = new DummyRepo(['hooks' => ['pre-push' => '# installed by CaptainHook ' . CH::MIN_REQ_INSTALLER]]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $inspector = new Inspector('pre-push', $io, $repo);
        $inspector->inspect();

        $this->assertTrue(true);
    }

    public function testInspectInvalid(): void
    {
        $this->expectException(Exception::class);

        $io    = $this->createIOMock();
        $dummy = new DummyRepo(['hooks' => ['pre-push' => '# installed by CaptainHook 3.10.4']]);
        $repo  = $this->createRepositoryMock($dummy->getRoot());
        $repo->method('getHooksDir')->willReturn($dummy->getHookDir());

        $inspector = new Inspector('pre-push', $io, $repo);
        $inspector->inspect();

        $this->assertTrue(true);
    }
}
