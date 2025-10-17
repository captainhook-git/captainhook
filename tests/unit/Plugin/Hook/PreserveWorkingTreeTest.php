<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Plugin\Hook;

use CaptainHook\App\Config\Action;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Hooks;
use CaptainHook\App\Mockery as CHMockery;
use CaptainHook\App\Runner\Hook;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Git\Operator\Diff;
use SebastianFeldmann\Git\Operator\Index;
use SebastianFeldmann\Git\Operator\Status;
use SebastianFeldmann\Git\Repository;
use SebastianFeldmann\Git\Status\Path;
use Symfony\Component\Filesystem\Filesystem;

class PreserveWorkingTreeTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    /**
     * @var Repository&MockObject
     */
    private $repo;

    /**
     * @var Status&MockObject
     */
    private $statusOperator;

    /**
     * @var Diff&MockObject
     */
    private $diffOperator;

    /**
     * @var Index&MockObject
     */
    private $indexOperator;

    /**
     * @var Filesystem&MockObject
     */
    private $filesystem;

    protected function setUp(): void
    {
        $this->repo = $this->createRepositoryMock();

        $this->statusOperator = $this->getMockBuilder(Status::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->diffOperator = $this->getMockBuilder(Diff::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexOperator = $this->getMockBuilder(Index::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->repo->method('getStatusOperator')->willReturn($this->statusOperator);
        $this->repo->method('getDiffOperator')->willReturn($this->diffOperator);
        $this->repo->method('getIndexOperator')->willReturn($this->indexOperator);

        $this->filesystem = $this->getMockBuilder(Filesystem::class)->getMock();
    }

    public function testBeforeAndAfterActionDoNothing(): void
    {
        $config = $this->createConfigMock();
        $io = $this->createIOMock();
        $pluginConfig = $this->createPluginConfigMock();

        $hook = $this->getMockBuilder(Hook::class)
            ->disableOriginalConstructor()
            ->getMock();

        $hook->expects($this->never())->method('getName');

        $plugin = new PreserveWorkingTree($this->filesystem);
        $plugin->configure($config, $io, $this->repo, $pluginConfig);

        $plugin->beforeAction($hook, new Action('foo'));
        $plugin->afterAction($hook, new Action('foo'));
    }

    public function testWithStatusPathsButZeroIntentToAddFiles(): void
    {
        $config = $this->createConfigMock();
        $io = $this->createIOMock();
        $pluginConfig = $this->createPluginConfigMock();

        $this->statusOperator->method('getWorkingTreeStatus')->willReturn([
            new Path('M ', 'foo/bar.php'),
            new Path('M ', 'foo/baz.php'),
        ]);

        $this->diffOperator->method('getUnstagedPatch')->willReturn(null);

        $this->indexOperator->expects($this->never())->method('removeFiles');
        $this->indexOperator->expects($this->never())->method('recordIntentToAddFiles');
        $this->statusOperator->expects($this->never())->method('restoreWorkingTree');
        $this->diffOperator->expects($this->never())->method('applyPatches');

        $hook = new class ($io, $config, $this->repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };

        $plugin = new PreserveWorkingTree($this->filesystem);
        $plugin->configure($config, $io, $this->repo, $pluginConfig);

        $plugin->beforeHook($hook);
        $plugin->afterHook($hook);
    }

    public function testRunHookWithIntentToAddFiles(): void
    {
        $config = $this->createConfigMock();
        $io = $this->createIOMock();
        $pluginConfig = $this->createPluginConfigMock();

        $statusPaths = [
            new Path('M ', 'foo/bar.php'),
            new Path(' A', 'foo/qux.php'),
            new Path('M ', 'foo/baz.php'),
            new Path(' A', 'foo/quux.php'),
        ];

        $this->statusOperator->method('getWorkingTreeStatus')->willReturn($statusPaths);
        $this->diffOperator->method('getUnstagedPatch')->willReturn(null);

        $io->expects($this->atLeastOnce())->method('write');

        $this->indexOperator
            ->expects($this->once())
            ->method('removeFiles')
            ->with(['foo/qux.php', 'foo/quux.php'], false, true);

        $this->indexOperator
            ->expects($this->once())
            ->method('recordIntentToAddFiles')
            ->with(['foo/qux.php', 'foo/quux.php']);

        $this->statusOperator->expects($this->never())->method('restoreWorkingTree');
        $this->diffOperator->expects($this->never())->method('applyPatches');

        $hook = new class ($io, $config, $this->repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };

        $plugin = new PreserveWorkingTree($this->filesystem);
        $plugin->configure($config, $io, $this->repo, $pluginConfig);

        $plugin->beforeHook($hook);
        $plugin->afterHook($hook);
    }

    public function testRunHookWithUnstagedChanges(): void
    {
        // This should not be set before this test runs.
        $this->assertFalse(getenv(PreserveWorkingTree::SKIP_POST_CHECKOUT_VAR));

        $config = $this->createConfigMock();
        $io = $this->createIOMock();
        $pluginConfig = $this->createPluginConfigMock();

        $unstagedChanges = 'foo bar baz';
        $patchFileConstraint = $this->matches(sys_get_temp_dir() . '/CaptainHook/patches/%d-%x.patch');

        $this->statusOperator->method('getWorkingTreeStatus')->willReturn([]);

        $this->indexOperator->expects($this->never())->method('removeFiles');
        $this->indexOperator->expects($this->never())->method('recordIntentToAddFiles');

        $this->diffOperator
            ->expects($this->once())
            ->method('getUnstagedPatch')
            ->willReturn($unstagedChanges);

        $this->filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with($patchFileConstraint, $unstagedChanges);

        $this->statusOperator
            ->expects($this->once())
            ->method('restoreWorkingTree')
            ->willReturnCallback(function (): bool {
                if (getenv(PreserveWorkingTree::SKIP_POST_CHECKOUT_VAR) != '1') {
                    $this->fail(PreserveWorkingTree::SKIP_POST_CHECKOUT_VAR . ' did not have the correct value');
                }
                return true;
            });

        $this->diffOperator
            ->expects($this->once())
            ->method('applyPatches')
            ->with($this->callback(function ($value) use ($patchFileConstraint): bool {
                $result = true;
                foreach ($value as $item) {
                    $result = $result && $patchFileConstraint->evaluate($item, '', true);
                }
                return $result;
            }))
            ->willReturn(true);

        $hook = new class ($io, $config, $this->repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };

        $plugin = new PreserveWorkingTree($this->filesystem);
        $plugin->configure($config, $io, $this->repo, $pluginConfig);

        $plugin->beforeHook($hook);
        $plugin->afterHook($hook);

        // This should not be set after this test is finished.
        $this->assertFalse(getenv(PreserveWorkingTree::SKIP_POST_CHECKOUT_VAR));
    }

    public function testRunHookWithUnstagedChangesAttemptsPatchMultipleTimes(): void
    {
        $config = $this->createConfigMock();
        $io = $this->createIOMock();
        $pluginConfig = $this->createPluginConfigMock();

        $unstagedChanges = 'foo bar baz';
        $patchFileConstraint = $this->matches(sys_get_temp_dir() . '/CaptainHook/patches/%d-%x.patch');

        $this->statusOperator->method('getWorkingTreeStatus')->willReturn([]);

        $this->indexOperator->expects($this->never())->method('removeFiles');
        $this->indexOperator->expects($this->never())->method('recordIntentToAddFiles');

        $this->diffOperator
            ->expects($this->once())
            ->method('getUnstagedPatch')
            ->willReturn($unstagedChanges);

        $this->filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with($patchFileConstraint, $unstagedChanges);

        $this->statusOperator
            ->expects($this->exactly(2))
            ->method('restoreWorkingTree')
            ->willReturn(true);

        $this->diffOperator
            ->expects($this->exactly(3))
            ->method('applyPatches')
            ->with($this->callback(function ($value) use ($patchFileConstraint): bool {
                $result = true;
                foreach ($value as $item) {
                    $result = $result && $patchFileConstraint->evaluate($item, '', true);
                }
                return $result;
            }))
            ->willReturnOnConsecutiveCalls(false, false, true);

        $hook = new class ($io, $config, $this->repo) extends Hook {
            protected string $hook = Hooks::PRE_COMMIT;
        };

        $plugin = new PreserveWorkingTree($this->filesystem);
        $plugin->configure($config, $io, $this->repo, $pluginConfig);

        $plugin->beforeHook($hook);
        $plugin->afterHook($hook);
    }

    public function testSkipActionsWhenEnvironmentVariableIsSet(): void
    {
        $config = $this->createConfigMock();
        $io = $this->createIOMock();
        $pluginConfig = $this->createPluginConfigMock();

        $this->statusOperator->method('getWorkingTreeStatus')->willReturn([]);
        $this->diffOperator->method('getUnstagedPatch')->willReturn(null);

        $this->indexOperator->expects($this->never())->method('removeFiles');
        $this->indexOperator->expects($this->never())->method('recordIntentToAddFiles');
        $this->statusOperator->expects($this->never())->method('restoreWorkingTree');
        $this->diffOperator->expects($this->never())->method('applyPatches');

        $hook = new class ($io, $config, $this->repo) extends Hook {
            protected string $hook = Hooks::POST_CHECKOUT;
        };

        $plugin = new PreserveWorkingTree($this->filesystem);
        $plugin->configure($config, $io, $this->repo, $pluginConfig);

        $this->assertFalse($hook->shouldSkipActions());

        putenv(PreserveWorkingTree::SKIP_POST_CHECKOUT_VAR . '=1');
        $plugin->beforeHook($hook);

        $this->assertTrue($hook->shouldSkipActions());

        // Unset the environment variable when finished with the test.
        putenv(PreserveWorkingTree::SKIP_POST_CHECKOUT_VAR);
    }
}
