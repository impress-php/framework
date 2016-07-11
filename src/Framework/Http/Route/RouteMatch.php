<?php
namespace Impress\Framework\Http\Route;

class RouteMatch
{
    private static $routesFile;

    public function __construct($routesFile = null)
    {
        $this->setRoutesFile($routesFile);
        $this->work();
    }

    private function setRoutesFile($routesFile)
    {
        if (is_file($routesFile)) {
            self::$routesFile = $routesFile;
        } else {
            self::$routesFile = app_path('Http' . DIRECTORY_SEPARATOR . 'Routes.php');
        }
    }

    private function work()
    {
        RouteCache::work(self::$routesFile);
    }
}
