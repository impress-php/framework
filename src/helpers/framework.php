<?php
if (!function_exists("config")) {
    function config($file)
    {
        return \Impress\Framework\Config\Config::get($file);
    }
}
