<?php
namespace Impress\Framework\Http;

use Symfony\Component\HttpFoundation\Request as VendorRequest;

class Request extends VendorRequest
{
    private static $staticRequest;

    public static function request(
        array $query = array(),
        array $request = array(),
        array $attributes = array(),
        array $cookies = array(),
        array $files = array(),
        array $server = array(),
        $content = null
    )
    {
        if (!(self::$staticRequest instanceof self)) {
            empty($query) && $query = $_GET;
            empty($request) && $request = $_POST;
            empty($cookies) && $cookies = $_COOKIE;
            empty($files) && $files = $_FILES;
            empty($server) && $server = $_SERVER;
            self::$staticRequest = new self($query, $request, $attributes, $cookies, $files, $server, $content);
        }
        return self::$staticRequest;
    }

    public static function clearRequest()
    {
        self::$staticRequest = null;
    }

    public function getCookie($name)
    {
        if (!is_null($cookieValue = $this->cookies->get($name))) {
            return Cookie::decryptCookieValue($cookieValue);
        }
        return null;
    }
}
