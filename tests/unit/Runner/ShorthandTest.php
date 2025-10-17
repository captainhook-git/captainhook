<?php

/**
 * This file is part of CaptainHook.
 *
 * (c) Sebastian Feldmann <sf@sebastian-feldmann.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CaptainHook\App\Runner;

use Exception;
use PHPUnit\Framework\TestCase;

class ShorthandTest extends TestCase
{
    /**
     * Checks if shorthands are identified correctly
     */
    public function testCanIdentifyShorthand()
    {
        // negative
        $this->assertFalse(Shorthand::isShorthand('foo'));
        $this->assertFalse(Shorthand::isShorthand('\\CaptainHook\\App'));
        $this->assertFalse(Shorthand::isShorthand('CaptainHook.'));
        $this->assertFalse(Shorthand::isShorthand('captainhook.sh'));

        // positive
        $this->assertTrue(Shorthand::isShorthand('CaptainHook.foo.fiz'));
        $this->assertTrue(Shorthand::isShorthand('captainhook.Bar.Baz'));
        $this->assertTrue(Shorthand::isShorthand('CAPTAINHOOK.FOO.BAR'));
    }

    /**
     * Check if invalid shorthand detection works
     */
    public function testDetectsInvalidActionShortHand(): void
    {
        $this->expectException(Exception::class);
        Shorthand::getActionClass('Captainhook.foo.bar.baz');
    }

    /**
     * Check if an invalid shorthand group is detected
     */
    public function testDetectsInvalidActionShorthandGroup(): void
    {
        $this->expectException(Exception::class);
        Shorthand::getActionClass('Captainhook.foo.bar');
    }

    /**
     * Check if an invalid action shorthand name is detected
     */
    public function testDetectsInvalidActionShorthandName(): void
    {
        $this->expectException(Exception::class);
        Shorthand::getActionClass('Captainhook.File.bar');
    }

    /**
     * Check if an invalid condition shorthand name is detected
     */
    public function testDetectsInvalidConditionShorthandName(): void
    {
        $this->expectException(Exception::class);
        Shorthand::getConditionClass('Captainhook.FileStaged.bar');
    }

    /**
     * Check if a valid action shorthand is mapped correctly
     */
    public function testFindsActionClassByShorthand(): void
    {
        $class = Shorthand::getActionClass('Captainhook.Branch.EnsureNaming');
        $this->assertTrue(str_contains($class, 'CaptainHook\App\Hook\Branch\Action\EnsureNaming'));
    }

    /**
     * Check if a valid condition shorthand is mapped correctly
     */
    public function testFindsConditionClassByShorthand(): void
    {
        $class = Shorthand::getConditionClass('Captainhook.Status.OnBranch');
        $this->assertTrue(str_contains($class, 'CaptainHook\App\Hook\Condition\Branch\On'));
    }
}
