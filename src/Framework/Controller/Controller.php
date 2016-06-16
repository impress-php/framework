<?php
namespace Impress\Framework\Controller;

use Impress\Framework\Response\Response;
use Symfony\Component\HttpFoundation\Request;

class Controller
{
    private static $request;
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
        if (!(self::$request instanceof Request)) {
            empty($query) && $query = $_GET;
            empty($request) && $request = $_POST;
            empty($cookies) && $cookies = $_COOKIE;
            empty($files) && $files = $_FILES;
            empty($server) && $server = $_SERVER;
            self::$request = new Request($query, $request, $attributes, $cookies, $files, $server, $content);
        }
        return self::$request;
    }

    public function clearRequest()
    {
        self::$request = null;
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
