<?php
namespace Impress\Framework\Config;
class Config
{
    private static $configs;
    private static $dir;

    private static function init_configs($file)
    {
        if (is_null(self::$dir)) {
            $env = env("ENV", "production");
            self::$dir = CONFIG_DIR . DIRECTORY_SEPARATOR . $env;
            if (!is_dir(self::$dir)) {
                self::$dir = CONFIG_DIR;
            }
        }

        $filename = self::$dir . DIRECTORY_SEPARATOR . $file . ".php";
        is_file($filename) && self::$configs[$file] = require_once($filename);
    }

    private static function get_file($file)
    {
        (isset(self::$configs[$file])) || self::init_configs($file);
        return self::$configs[$file];
    }

    public static function get($parameters)
    {
        $parameters = explode(".", $parameters);
        $file = $parameters[0];
        $config = $config_file = self::get_file($file);
        if (count($parameters) === 1) {
            return $config;
        } else {
            array_shift($parameters);
            foreach ($parameters as $p) {
                $config = isset($config[$p]) ? $config[$p] : null;
            }
            return $config;
        }
    }
}
