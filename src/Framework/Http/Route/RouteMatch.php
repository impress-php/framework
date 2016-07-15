<?php
namespace Impress\Framework\Http\Route;

use Impress\Framework\Http\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

class RouteMatch
{
    const ROUTES_KEY_ROUTE_NAME = "route_name";
    const ROUTES_KEY_ROUTE = "route";
    const ROUTE_AUTO_NAME_PREFIX = 'ROUTE:';

    private static $route_name_autoincrement = 0;
    public static $routes = array();
    private static $routesFile;
    private $routeCollection;

    public function __construct($routesFile = null)
    {
        $this->setRoutesFile($routesFile);
    }

    private function setRoutesFile($routesFile)
    {
        if (is_file($routesFile)) {
            self::$routesFile = $routesFile;
        } else {
            self::$routesFile = app_path('Http' . DIRECTORY_SEPARATOR . 'routes.php');
        }
    }

    private function addRoutesToRouteCollection()
    {
        if (!($this->routeCollection instanceof RouteCollection)) {
            $this->routeCollection = new RouteCollection();
        }

        foreach (self::$routes as $k => $route) {
            $VendorRoute = $route[self::ROUTES_KEY_ROUTE];
            $route_name = $route[self::ROUTES_KEY_ROUTE_NAME];

            $this->routeCollection->add($route_name, $VendorRoute);
        }
    }

    /**
     * @return RouteParametersItem
     */
    public function work()
    {
        RouteCache::work(self::$routesFile);
        $this->addRoutesToRouteCollection();

        $context = new RequestContext();
        $context->fromRequest(Request::request());

        $matcher = new UrlMatcher($this->routeCollection, $context);
        $parameters = $matcher->matchRequest(Request::request());

        return (new RouteParametersItem($parameters));
    }

    public static function addRoute(array $routeArgs)
    {
        foreach ([
                     'controller',
                     'middleware',
                     'name',
                     'as',
                     'path',
                     'defaults',
                     'requirements',
                     'options',
                     'host',
                     'schemes',
                     'methods',
                     'condition'
                 ] as $p) {

            switch ($p) {
                case 'path':
                case 'host':
                case 'condition':
                    $default_value = '';
                    break;
                case 'controller':
                case 'middleware':
                case 'name':
                case 'as':
                    $default_value = null;
                    break;
                default:
                    $default_value = array();
                    break;
            }

            ${$p} = get_array_item($routeArgs, RouteParametersItem::ROUTE_PARAMETER_PREFIX . $p, $default_value);

            if (!isset($name) && isset($as)) {
                $name = $as;
            }
            if (isset($path)) {
                $path = strtr($path, [
                    '//' => '/'
                ]);
            }
        }

        $_defaults = [
            RouteParametersItem::ROUTE_PARAMETER_CONTROLLER_KEY => $controller,
            RouteParametersItem::ROUTE_PARAMETER_MIDDLEWARE_KEY => $middleware
        ];

        $route = new SymfonyRoute(
            $path,
            array_merge($defaults, $_defaults),
            $requirements,
            $options,
            $host,
            $schemes,
            $methods,
            $condition
        );

        $name = self::autoRouteName($name);

        if (isset(self::$routes[$name])) {
            throw new \RuntimeException("The route name '{$name}' already exists.");
        }

        $_route = array(
            self::ROUTES_KEY_ROUTE => $route,
            self::ROUTES_KEY_ROUTE_NAME => $name
        );

        self::$routes[$name] = $_route;
        return $_route;
    }

    private static function autoRouteName($name)
    {
        if (is_null($name)) {
            self::$route_name_autoincrement++;
            return self::ROUTE_AUTO_NAME_PREFIX . self::$route_name_autoincrement;
        }
        return $name;
    }

    public static function clearRoutes()
    {
        self::$routes = array();
    }

    public static function setRoutes(array $routes)
    {
        self::$routes = $routes;
    }

    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * @param $routeName
     * @return SymfonyRoute
     */
    public static function getRoute($routeName)
    {
        return self::$routes[$routeName]['route'];
    }
}
