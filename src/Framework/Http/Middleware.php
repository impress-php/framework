<?php
namespace Impress\Framework\Http;

class Middleware
{
    private static $middlewareMapFile = HTTP_DIR . DIRECTORY_SEPARATOR . "middleware.php";
    private static $middlewares = array();
    private static $map;

    public static function addMiddleware($middleware)
    {
        if (is_null($middleware)) {
            return;
        }

        if (strpos($middleware, "\\") === false) {
            $middleware = self::getMiddlewareFromMap($middleware);
        }

        if (is_array($middleware)) {
            foreach ($middleware as $m) {
                self::addMiddleware($m);
            }
        }

        ($middleware && is_string($middleware)) && array_push(self::$middlewares, $middleware);
    }

    private static function getMiddlewareFromMap($middlewares)
    {
        if (is_null(self::$map)) {
            is_file(self::$middlewareMapFile) && self::$map = require_once(self::$middlewareMapFile);
        }
        if (!isset(self::$map[$middlewares])) {
            return null;
            throw new \RuntimeException("The middleware '{$middlewares}' can not found in map file [" . self::$middlewareMapFile . "].");
        }
        return self::$map[$middlewares];
    }

    public static function getMiddlewares()
    {
        self::$middlewares = array_unique(self::$middlewares);
        return self::$middlewares;
    }

    public static function handle()
    {
        self::$middlewares = self::getMiddlewares();
        if (!empty(self::$middlewares)) {
            foreach (self::$middlewares as $m) {
                $instance = new $m;
                $r = call_user_func([$instance, "handle"]);
                if (!is_bool($r)) {
                    return $r;
                }
            }
        }
        return true;
    }
}
