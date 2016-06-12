<?php
namespace Impress\Framework\Config;
class Config
{
    private static $configs;

    private static function init_configs($file)
    {
        $filename = CONFIG_DIR . DIRECTORY_SEPARATOR . $file . ".php";
        is_file($filename) && self::$configs[$file] = require_once($filename);
    }

    public static function get($file)
    {
        (isset(self::$configs[$file])) || self::init_configs($file);
        (is_null(self::$configs[$file])) && self::init_configs($file);
        return self::$configs[$file];
    }
}