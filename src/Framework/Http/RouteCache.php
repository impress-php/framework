<?php
namespace Impress\Framework\Http;

class RouteCache
{
    const ROUTES_CACHE_FILENAME = CACHE_DIR . DIRECTORY_SEPARATOR . "Routes.php";

    private static $cacheRoutesContent;

    private static function cacheContent()
    {
        $routes = Route::getRoutes();
        try {
            $s = serialize($routes);
        } catch (\Exception $e) {
            // todo: We could be create a log. ['Serialization of 'Closure' is not allowed']
            return false;
        }

        $routesFile = Bootstrap::getRoutesFile();
        if (!is_file($routesFile)) {
            return false;
        }
        $routesFileUpdateTimeStamp = filemtime($routesFile);
        if (!$routesFileUpdateTimeStamp) {
            return false;
        }

        $content = "<?php\n";
        $content .= 'return ["';
        $content .= addslashes($s);
        $content .= '",' . $routesFileUpdateTimeStamp . '];';
        return $content;
    }

    private static function isFromCache()
    {
        $file = self::ROUTES_CACHE_FILENAME;
        if (!is_file($file)) {
            return false;
        }
        $routesFile = Bootstrap::getRoutesFile();
        $content_arr = require_once($file);
        self::$cacheRoutesContent = unserialize($content_arr[0]);

        $cacheFileUpdateTimeStamp = $content_arr[1];
        $routesFileUpdateTimeStamp = filemtime($routesFile);
        if (!$routesFileUpdateTimeStamp) {
            return false;
        }

        if ($routesFileUpdateTimeStamp > $cacheFileUpdateTimeStamp) {
            return false;
        }

        return true;
    }

    private static function writeCacheFile()
    {
        $cacheContent = self::cacheContent();
        if (!$cacheContent) {
            return;
        }
        file_put_contents(self::ROUTES_CACHE_FILENAME, $cacheContent);
    }

    public static function makeRoutes()
    {
        $routesFile = Bootstrap::getRoutesFile();
        if (!self::isFromCache()) {
            if (is_file($routesFile)) {
                @unlink(self::ROUTES_CACHE_FILENAME);
                require_once($routesFile);
                self::writeCacheFile();
            }
        } else {
            Route::setRoutes(self::$cacheRoutesContent);
        }
    }
}

