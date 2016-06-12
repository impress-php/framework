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

if (!function_exists("document_uri")) {
    function document_uri()
    {
        if (($uri_query_mark = strpos($_SERVER['REQUEST_URI'], "?")) <= 0) {
            return $_SERVER['REQUEST_URI'];
        }
        return substr($_SERVER['REQUEST_URI'], 0, $uri_query_mark);
    }
}