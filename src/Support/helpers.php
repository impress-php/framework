<?php
use \Impress\Support\Str;
use \Impress\Framework\DotFile\Config;
use \Impress\Framework\DotFile\Lang;

if (!function_exists("config")) {
    function config($parameters, $default = null)
    {
        return Config::get($parameters, $default);
    }
}

if (!function_exists("lang")) {
    function lang($parameters, $dir = '', $default = null)
    {
        return Lang::get($parameters, $dir, $default);
    }
}

if (!function_exists("is_production")) {
    function is_production()
    {
        return boolval((env('ENV', 'production') == "production"));
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) return value($default);

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return null;
        }

        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }
        return $value;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('get_array_item')) {
    /**
     * Return the default item of the array.
     *
     * @param  array $arr
     * @param  mixed $key
     * @param  mixed $default
     * @return mixed
     */
    function get_array_item(array $arr, $key, $default = null)
    {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }
}

if (!function_exists('write_file')) {
    /**
     * @param $filename
     * @param $content
     * @return bool
     */
    function write_file($filename, $content)
    {
        if (!is_file($filename)) {
            return boolval(file_put_contents($filename, $content));
        } else {
            $filename_tmp = storage_path('cache') . DIRECTORY_SEPARATOR . Str::guid() . '.tmp';
            $write = file_put_contents($filename_tmp, $content);
            $move = rename($filename_tmp, $filename);
            return boolval($write && $move);
        }
    }
}

if (!function_exists('root_path')) {
    function root_path($path = '')
    {
        return IMPRESS_PHP_ROOT_PATH . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('vendor_path')) {
    function vendor_path($path = '')
    {
        return root_path("vendor") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('config_path')) {
    function config_path($path = '')
    {
        return root_path("config") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('public_path')) {
    function public_path($path = '')
    {
        return root_path("public") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('resources_path')) {
    function resources_path($path = '')
    {
        return root_path("resources") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return root_path("storage") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('app_path')) {
    function app_path($path = '')
    {
        return root_path("app") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
