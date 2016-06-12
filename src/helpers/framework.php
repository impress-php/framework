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
