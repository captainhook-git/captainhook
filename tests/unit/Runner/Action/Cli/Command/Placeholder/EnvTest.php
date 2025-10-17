<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner\Action\Cli\Command\Placeholder;

use CaptainHook\App\Console\IO\Mockery as IOMockery;
use CaptainHook\App\Config\Mockery as ConfigMockery;
use CaptainHook\App\Mockery as AppMockery;
use PHPUnit\Framework\TestCase;

class EnvTest extends TestCase
{
    use IOMockery;
    use AppMockery;
    use ConfigMockery;

    public function testEnvValue(): void
    {
        $_ENV['foo'] = 'bar';

        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();

        $placeholder = new Env($io, $config, $repo);
        $result      = $placeholder->replacement(['value-of' => 'foo']);

        $this->assertEquals('bar', $result);

        unset($_ENV['foo']);
    }

    public function testNoValueOf(): void
    {
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();

        $placeholder = new Env($io, $config, $repo);
        $result      = $placeholder->replacement([]);

        $this->assertEquals('', $result);
    }

    public function testDefault(): void
    {
        $io     = $this->createIOMock();
        $repo   = $this->createRepositoryMock();
        $config = $this->createConfigMock();

        $placeholder = new Env($io, $config, $repo);
        $result      = $placeholder->replacement(['value-of' => 'MY_SUPER_ENV_VAR', 'default' => 'my-default']);

        $this->assertEquals('my-default', $result);
    }
}
