<?php
if (!function_exists("config")) {
    function config($parameters)
    {
        return \Impress\Framework\Config\Config::get($parameters);
    }
}

if (!function_exists("is_production")) {
    function is_production()
    {
        return boolval((getenv('ENV') == "production"));
    }
}