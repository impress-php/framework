<?php
namespace Impress\Framework;

class Globals
{
    private static $prefix = 'impress_global_vars_';

    public static function set($key, $val)
    {
        $GLOBALS[self::$prefix . $key] = $val;
    }

    public static function get($key)
    {
        return $GLOBALS[self::$prefix . $key];
    }
}
