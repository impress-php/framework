<?php
namespace Impress\Framework\Http;

use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

class View
{
    const ENGINE_AUTO = 0;
    const ENGINE_TWIG = 1;
    const ENGINE_SYMFONY = 2;

    public static $VIEWS_DIR;
    public static $CACHE_VIEWS_DIR;

    public static $TWIG_OPTION_DEBUG;
    public static $TWIG_OPTION_AUTO_RELOAD;
    public static $TWIG_OPTION_CHARSET;

    public static function make($name, array $data = array(), $engine = self::ENGINE_AUTO)
    {
        is_null(self::$TWIG_OPTION_DEBUG) && self::$TWIG_OPTION_DEBUG = env("TWIG_OPTION_DEBUG", 0);
        is_null(self::$TWIG_OPTION_AUTO_RELOAD) && self::$TWIG_OPTION_AUTO_RELOAD = env("TWIG_OPTION_AUTO_RELOAD", 1);
        is_null(self::$TWIG_OPTION_CHARSET) && self::$TWIG_OPTION_CHARSET = env("TWIG_OPTION_CHARSET", "UTF-8");

        if ($engine == self::ENGINE_AUTO) {
            $name_ext = substr($name, strrpos($name, '.'));
            switch ($name_ext) {
                case ".php":
                    $engine = self::ENGINE_SYMFONY;
                    break;
                case ".twig":
                default:
                    $engine = self::ENGINE_TWIG;
                    break;
            }
        }

        $content = "";

        is_null(self::$VIEWS_DIR) && self::$VIEWS_DIR = resources_path("views");

        switch ($engine) {
            case self::ENGINE_SYMFONY:
                $loader = new FilesystemLoader(self::$VIEWS_DIR . DIRECTORY_SEPARATOR . "%name%");
                $templating = new PhpEngine(new TemplateNameParser(), $loader);
                $content = $templating->render($name, $data);
                break;
            case self::ENGINE_TWIG:
                $loader = new \Twig_Loader_Filesystem(self::$VIEWS_DIR);
                $twig = new \Twig_Environment($loader, array(
                    'cache' => self::$CACHE_VIEWS_DIR ?: storage_path("cache" . DIRECTORY_SEPARATOR . "views"),
                    'auto_reload' => self::$TWIG_OPTION_AUTO_RELOAD,
                    'debug' => self::$TWIG_OPTION_DEBUG,
                    'charset' => self::$TWIG_OPTION_CHARSET
                ));
                $content = $twig->render($name, $data);
                break;
        }

        return $content;
    }
}