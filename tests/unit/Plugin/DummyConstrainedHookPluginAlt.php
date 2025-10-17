<?php

namespace CaptainHook\App\Plugin;

use CaptainHook\App\Hook\Constrained;
use CaptainHook\App\Hook\Restriction;

class DummyConstrainedHookPluginAlt extends DummyHookPlugin implements Constrained
{
    /**
     * @var Restriction|null
     */
    public static ?Restriction $restriction;

    public static function getRestriction(): Restriction
    {
        return self::$restriction;
    }
}
