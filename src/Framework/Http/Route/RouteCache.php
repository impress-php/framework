<?php
namespace Impress\Framework\Http\Route;

class RouteCache
{
    private static $routesCacheFilename;

    private static $cacheRoutesContent;

    private static function getRoutesCacheFilename()
    {
        if (is_null(self::$routesCacheFilename)) {
            self::$routesCacheFilename = storage_path('cache' . DIRECTORY_SEPARATOR . 'routes.php');
        }
        return self::$routesCacheFilename;
    }

    private static function cacheContent($routesFile)
    {
        $routes = RouteMatch::getRoutes();
        try {
            $s = serialize($routes);
        } catch (\Exception $e) {
            // todo: We could be create a log. ['Serialization of 'Closure' is not allowed']
            return false;
        }

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

    private static function isFromCache($routesFile)
    {
        $file = self::getRoutesCacheFilename();
        if (!is_file($file)) {
            return false;
        }
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

    private static function writeCacheFile($routesFile)
    {
        $cacheContent = self::cacheContent($routesFile);
        if (!$cacheContent) {
            return;
        }
        write_file(self::getRoutesCacheFilename(), $cacheContent);
    }

    private static function workRoute($routesFile)
    {
        if (is_file($routesFile)) {
            require_once($routesFile);
            return true;
        } else {
            throw new \RuntimeException("Route file [{$routesFile}] can not found.");
        }
    }

    private static function makeCache($routesFile)
    {
        if (!self::isFromCache($routesFile)) {
            if (is_file($routesFile)) {
                self::workRoute($routesFile);
                self::writeCacheFile($routesFile);
                return true;
            } else {
                throw new \RuntimeException("Route file [{$routesFile}] can not found.");
            }
        }
        return false;
    }

    public static function work($routesFile)
    {
        if (env("ROUTES_NO_CACHE", false)) {
            self::workRoute($routesFile);
        } else {
            self::makeCache($routesFile) ?: RouteMatch::setRoutes(self::$cacheRoutesContent);
        }
    }
}
