<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Git\Rev;

use PHPUnit\Framework\TestCase;

/**
 * Class PrePushTest
 *
 * @package CaptainHook
 * @author  Sebastian Feldmann <sf@sebastian-feldmann.info>
 * @link    https://github.com/captainhook-git/captainhook
 * @since   Class available since Release 5.15.0
 */
class PrePushTest extends TestCase
{
    public function testGetter(): void
    {
        $ref = new PrePush('refs/heads/main', '12345', 'main');

        $this->assertEquals('refs/heads/main', $ref->head());
        $this->assertEquals('12345', $ref->hash());
        $this->assertEquals('main', $ref->branch());
        $this->assertEquals('12345', $ref->id());
    }
}
