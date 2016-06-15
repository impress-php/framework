<?php
namespace Impress\Framework\Controller;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

class Controller
{
    private static $request;

    public function __construct()
    {

    }

    protected function request(
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
}