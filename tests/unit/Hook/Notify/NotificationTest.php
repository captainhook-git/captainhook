<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Hook\Notify;

use PHPUnit\Framework\TestCase;

class NotificationTest extends TestCase
{
    public function testBanner()
    {
        $banner = PHP_EOL
                . '<error>         </error>' . PHP_EOL
                . '<error>  </error>     <error>  </error>' . PHP_EOL
                . '<error>  </error>  x  <error>  </error>' . PHP_EOL
                . '<error>  </error> xxx <error>  </error>' . PHP_EOL
                . '<error>  </error> xx  <error>  </error>' . PHP_EOL
                . '<error>  </error>     <error>  </error>' . PHP_EOL
                . '<error>         </error>' . PHP_EOL;

        $notification = new Notification(['x', 'xxx', 'xx']);
        $this->assertEquals($banner, $notification->banner());
    }
}
