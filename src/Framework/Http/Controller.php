<?php
namespace Impress\Framework\Http;

class Controller
{
    private static $response;

    public function __construct()
    {

    }

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
}
