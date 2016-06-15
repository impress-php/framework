<?php
namespace Impress\Framework\Controller;

use Impress\Framework\View\View;
use Symfony\Component\HttpFoundation\Cookie;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

class Controller
{
    private static $request;
    private static $response;

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

    protected function cookie_set(Response $response, $name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true, $raw = false)
    {
        $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw);
        $response->headers->setCookie($cookie);
    }

    protected function cookie_get()
    {

    }

    protected function response($content = "", $statusCode = 200, array $headers = array())
    {
        $response = new Response();
        $response->setContent($content);
        $response->setStatusCode($statusCode);
        $response->headers->add($headers);
        return $response;
    }

    protected function view($name, array $data = array(), $statusCode = 200, array $headers = array(), $engine = View::ENGINE_AUTO)
    {
        $content = View::make($name, $data, $engine);
        return $this->response($content, $statusCode, $headers);
    }

    protected function json(array $data, $statusCode = 200, array $headers = array(
        "Content-Type" => "application/json"
    ))
    {
        $content = json_encode($data);
        return $this->response($content, $statusCode, $headers);
    }
}
