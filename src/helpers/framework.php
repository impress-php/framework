<?php
if (!function_exists("config")) {
    function config($file)
    {
        return \Impress\Framework\Config\Config::get($file);
    }
}

if (!function_exists("is_production")) {
    function is_production()
    {
        return boolval((getenv('ENV') == "production"));
    }
}

if (!function_exists("response")) {
    function response($content = "", $statusCode = 200, array $headers = array())
    {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setContent($content);
        $response->setStatusCode($statusCode);
        $response->headers->add($headers);
        return $response;
    }
}

if (!function_exists("view")) {
    function view($name, array $data = array(), $statusCode = 200, array $headers = array(), $engine = \Impress\Framework\View\View::ENGINE_AUTO)
    {
        $content = \Impress\Framework\View\View::make($name, $data, $engine);
        return response($content, $statusCode, $headers);
    }
}

if (!function_exists("json")) {
    function json(array $data, $statusCode = 200, array $headers = array(
        "Content-Type" => "application/json"
    ))
    {
        $content = json_encode($data);
        return response($content, $statusCode, $headers);
    }
}

