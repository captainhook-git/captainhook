<?php

/**
 * This file is part of CaptainHook
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Config;

use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    public function testGet(): void
    {
        $options = new Options(['foo' => 'bar']);

        $this->assertEquals('bar', $options->get('foo'));
    }

    public function testGetAll(): void
    {
        $options = new Options(['foo']);

        $this->assertEquals(['foo'], $options->getAll());
    }
}
