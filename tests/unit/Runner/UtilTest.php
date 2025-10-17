<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner;

use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function testGetTypePHP(): void
    {
        $this->assertEquals('php', Util::getExecType('\\Foo\\Bar'));
        $this->assertEquals('php', Util::getExecType('\\Foo\\Bar::baz'));
        $this->assertEquals('php', Util::getExecType('\\Fiz'));
    }

    public function testGetTypeCli(): void
    {
        $this->assertEquals('cli', Util::getExecType('./my-binary'));
        $this->assertEquals('cli', Util::getExecType('phpunit'));
        $this->assertEquals('cli', Util::getExecType('~/composer install'));
        $this->assertEquals('cli', Util::getExecType('echo foo'));
        $this->assertEquals('cli', Util::getExecType('/usr/local/bin/phpunit.phar'));
    }

    public function testIsTypeValid(): void
    {
        $this->assertTrue(Util::isTypeValid('php'));
        $this->assertTrue(Util::isTypeValid('cli'));
        $this->assertFalse(Util::isTypeValid('foo'));
    }

    public function testCanReadEnvVar(): void
    {
        $_ENV['foo'] = 'bar';
        $this->assertEquals('bar', Util::getEnv('foo'));
        unset($_ENV['foo']);
    }

    public function testUsesServerSuperglobalAsFallback(): void
    {
        $_SERVER['foo'] = 'bar';
        $this->assertEquals('bar', Util::getEnv('foo'));
        unset($_SERVER['foo']);
    }

    public function testReturnsDefaultIdEnvNotSet(): void
    {
        $this->assertEquals('baz', Util::getEnv('fiz', 'baz'));
    }
}
