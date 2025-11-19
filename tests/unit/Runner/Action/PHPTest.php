<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Action;

use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Event\Dispatcher;
use CaptainHook\App\Exception\ActionNotApplicable;
use CaptainHook\App\Mockery as CHMockery;
use Exception;
use PHPUnit\Framework\TestCase;

class PHPTest extends TestCase
{
    use ConfigMockery;
    use IOMockery;
    use CHMockery;

    public function testExecuteSuccess(): void
    {
        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);
        $class  = DummyPHPSuccess::class;

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-commit', $events);
        $php->execute($config, $io, $repo, $action);
    }

    public function testExecuteEventSubscriber(): void
    {
        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);
        $class  = DummyPHPSubscriber::class;

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-commit', $events);
        $php->execute($config, $io, $repo, $action);
    }

    public function testExecuteConstraintApplicable(): void
    {
        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);
        $class  = DummyPHPConstraint::class;

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-commit', $events);
        $php->execute($config, $io, $repo, $action);
    }

    public function testExecuteConstraintNotApplicable(): void
    {
        $this->expectException(ActionNotApplicable::class);

        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);
        $class  = DummyPHPConstraint::class;

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-push', $events);
        $php->execute($config, $io, $repo, $action);
    }

    public function testExecuteFailure(): void
    {
        $this->expectException(Exception::class);

        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);
        $class  = DummyPHPFailure::class;

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-commit', $events);
        $php->execute($config, $io, $repo, $action);
    }

    public function testExecuteError(): void
    {
        $this->expectException(Exception::class);

        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);
        $class  = DummyPHPError::class;

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-commit', $events);
        $php->execute($config, $io, $repo, $action);
    }

    public function testExecuteByShorthand(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/debugging/i');

        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);
        $class  = 'CaptainHook.Debug.fail';

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-commit', $events);
        $php->execute($config, $io, $repo, $action);
    }

    public function testExecuteNoAction(): void
    {
        $this->expectException(Exception::class);

        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);
        $class  = DummyNoAction::class;

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-commit', $events);
        $php->execute($config, $io, $repo, $action);
    }

    public function testExecuteStaticClassNotFound(): void
    {
        $this->expectException(Exception::class);

        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);

        $class = '\\Fiz::baz';

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-commit', $events);
        $php->execute($config, $io, $repo, $action);
    }

    public function testExecuteStaticMethodNotFound(): void
    {
        $this->expectException(Exception::class);

        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);

        $class = '\\CaptainHook\\App\\Runner\\Action\\DummyNoAction::foo';

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-commit', $events);
        $php->execute($config, $io, $repo, $action);
    }

    public function testExecuteStaticSuccess(): void
    {
        $config = $this->createConfigMock();
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $action = $this->createActionConfigMock();
        $events = new Dispatcher($io, $config, $repo);

        $class = '\\CaptainHook\\App\\Runner\\Action\\DummyPHPSuccess::executeStatic';

        $action->expects($this->once())->method('getAction')->willReturn($class);

        $php = new PHP('pre-commit', $events);
        $php->execute($config, $io, $repo, $action);
    }
}
