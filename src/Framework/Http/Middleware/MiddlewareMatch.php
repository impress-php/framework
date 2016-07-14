<?php
namespace Impress\Framework\Http\Middleware;

use Impress\Framework\Http\Route\RouteMatch;

class MiddlewareMatch
{
    private static $middlewares = array();
    private static $map;

    public static function addMiddleware($middleware, array $options = [])
    {
        if (is_null($middleware)) {
            return;
        }

        if (is_string($middleware) && strpos($middleware, "\\") === false) {
            $middleware = self::getMiddlewareFromMap($middleware);
        }

        if (is_array($middleware)) {
            foreach ($middleware as $m) {
                self::addMiddleware($m, $options);
            }
            return;
        }

        if (is_string($middleware)) {
            $middleware = new MiddlewareItem($middleware, $options);
        }

        array_push(self::$middlewares, $middleware);
    }

    private static function getMiddlewareFromMap($middlewares)
    {
        $middlewareMapFile = app_path("Http" . DIRECTORY_SEPARATOR . "middleware.php");

        if (is_null(self::$map)) {
            is_file($middlewareMapFile) && self::$map = require_once($middlewareMapFile);
        }
        if (!isset(self::$map[$middlewares])) {
            throw new \RuntimeException("The middleware '{$middlewares}' can not found in map file '" . $middlewareMapFile . "'; ");
        }
        return self::$map[$middlewares];
    }

    public static function getMiddlewares()
    {
        return self::$middlewares;
    }

    private static function isDealWork($methodName, array $only, array $except)
    {
        if (empty($only) && empty($except)) {
            return true;
        }
        if (!empty($only) && empty($except)) {
            if (in_array($methodName, $only)) {
                return true;
            }
            return false;
        }
        if (empty($only) && !empty($except)) {
            if (!in_array($methodName, $except)) {
                return true;
            }
            return false;
        }
        if (!empty($only) && !empty($except)) {
            if (in_array($methodName, $only)) {
                return true;
            }
            if (in_array($methodName, $except)) {
                return false;
            }
        }
        return true;
    }

    public static function work($parameters)
    {
        // add middleware from route
        $routeMiddleware = RouteMatch::getMiddleware($parameters);
        if ($routeMiddleware) {
            self::addMiddleware($routeMiddleware);
        }

        // do work
        if (!empty(self::$middlewares)) {
            foreach (self::$middlewares as $m) {
                $class = $m->getMiddleware();
                $only = $m->getOnly();
                $except = $m->getExcept();
                if (self::isDealWork(RouteMatch::getController($parameters)[1], $only, $except)) {
                    $instance = new $class($parameters);
                    $r = call_user_func([$instance, "handle"]);
                    if (!is_bool($r)) {
                        return $r;
                    }
                }
            }
        }
        return true;
    }
}
