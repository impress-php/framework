<?php
namespace Impress\Framework\DotFile;

class Lang extends DotFile
{
    private static $dir;

    public static function get($dir, $parameters, $default = null)
    {
        if (is_null(self::$dir)) {
            $dir = resources_path('lang') . DIRECTORY_SEPARATOR . $dir;
            self::$dir = is_dir($dir) ? $dir : resources_path('lang');
        }

        $value = parent::_get(self::$dir, $parameters);
        return !is_null($value) ? $value : parent::_value($default);
    }
}
