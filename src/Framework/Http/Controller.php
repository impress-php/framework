<?php
namespace Impress\Framework\Http;

use Impress\Framework\Http\Session\Session;

class Controller
{
    private static $response;

    public function request(
        array $query = array(),
        array $request = array(),
        array $attributes = array(),
        array $cookies = array(),
        array $files = array(),
        array $server = array(),
        $content = null
    )
    {
        return Request::request($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    public function session(
        array $query = array(),
        array $request = array(),
        array $attributes = array(),
        array $cookies = array(),
        array $files = array(),
        array $server = array(),
        $content = null
    )
    {
        return $this->request($query, $request, $attributes, $cookies, $files, $server, $content)->getSession();
    }

    public function session_start(
        array $query = array(),
        array $request = array(),
        array $attributes = array(),
        array $cookies = array(),
        array $files = array(),
        array $server = array(),
        $content = null
    )
    {
        $session = new Session(getenv("SESSION_DRIVER"));
        $this->request($query, $request, $attributes, $cookies, $files, $server, $content)->setSession($session);
        session_start();
    }

    public function clearRequest()
    {
        Request::clearRequest();
    }

    public function response()
    {
        if (!(self::$response instanceof Response)) {
            self::$response = new Response();
        }
        return self::$response;
    }

    public function clearResponse()
    {
        self::$response = null;
    }

    public function middleware($middleware)
    {
        Middleware::addMiddleware($middleware);
    }
}
