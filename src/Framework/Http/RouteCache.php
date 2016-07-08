<?php
namespace Impress\Framework\Http;

class RouteCache
{
    private static $routesCacheFilename;

    private static $cacheRoutesContent;

    private static function getRoutesCacheFilename()
    {
        if (is_null(self::$routesCacheFilename)) {
            self::$routesCacheFilename = storage_path('cache' . DIRECTORY_SEPARATOR . 'Routes.php');
        }
        return self::$routesCacheFilename;
    }

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
        $file = self::getRoutesCacheFilename();
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
        file_put_contents(self::getRoutesCacheFilename(), $cacheContent);
    }

    public static function makeRoutes()
    {
        $routesFile = Bootstrap::getRoutesFile();
        if (!self::isFromCache()) {
            if (is_file($routesFile)) {
                @unlink(self::getRoutesCacheFilename());
                require_once($routesFile);
                self::writeCacheFile();
            }
        } else {
            Route::setRoutes(self::$cacheRoutesContent);
        }
    }
}

