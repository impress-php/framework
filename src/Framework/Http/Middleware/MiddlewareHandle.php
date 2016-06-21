<?php
namespace Impress\Framework\Http\Middleware;

use Impress\Framework\Http\Response;

class MiddlewareHandle
{
    private static $middlewares = array();

    public static function addMiddleware($middleware)
    {
        if (is_array($middleware)) {
            foreach ($middleware as $m) {
                self::addMiddleware($m);
            }
        }
        array_push(self::$middlewares, $middleware);
    }

    public static function handle()
    {
        self::$middlewares = array_unique(self::$middlewares);
        if (!empty(self::$middlewares)) {
            foreach (self::$middlewares as $m) {
                self::dealHandle($m);
            }
        }
    }

    private static function dealHandle($middleware)
    {
        $class = new $middleware;
        $r = $class->handle();
        if ($r !== true) {
            if ($r instanceof Response) {
                $r->send();
            } else {
                $response = new Response();
                $response->setContent($r);
                $response->setStatusCode(200);
                $response->send();
            }
            exit;
        }
    }
}
