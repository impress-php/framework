<?php
namespace Impress\Framework\DotFile;

class Config extends DotFile
{
    private static $dir;

    public static function get($parameters, $default = null)
    {
        if (is_null(self::$dir)) {
            $dir = config_path(env("ENV", "production"));
            self::$dir = is_dir($dir) ? $dir : config_path();
        }

        $value = parent::_get(self::$dir, $parameters);
        return !is_null($value) ? $value : parent::_value($default);
    }
}
