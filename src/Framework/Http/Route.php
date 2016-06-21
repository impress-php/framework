<?php
namespace Impress\Framework\Http;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route as VendorRoute;
use Symfony\Component\Routing\RouteCollection;

class Route
{
    const ROUTES_KEY_ROUTE_NAME = "route_name";
    const ROUTES_KEY_ROUTE = "route";

    private static $routes = array();
    private static $routeCollection;
    private static $groupAttributes = array();
    private static $groupAttributesKey = 0;
    private static $route_name_id = 0;

    private static function addRoute($path, array $defaults = array(), array $requirements = array(), array $options = array(), $host = '', $schemes = array(), $methods = array(), $condition = '')
    {
        $routeName = isset($defaults['name']) ? $defaults['name'] : "";
        $prefix = isset($defaults['prefix']) ? $defaults['prefix'] : "";

        if (!empty(self::$groupAttributes)) {
            $prefix_arr = array();
            foreach (self::$groupAttributes as $ga) {
                if (isset($ga['prefix'])) {
                    array_push($prefix_arr, $ga['prefix']);
                }

                if (isset($ga['middleware'])) {
                    if (!isset($defaults['middleware'])) {
                        $defaults['middleware'] = array();
                    }
                    $defaults['middleware'] = array_merge($ga['middleware'], $defaults['middleware']);
                }
            }
            $prefix = implode("", $prefix_arr) . $prefix;
        }

        $path = trim(trim($path), '/');
        $prefix = trim(trim($prefix), '/');
        ('' !== $prefix) && $path = $prefix . "/" . $path;
        $path = "/" . $path;
        $path = rtrim($path, "/");

        $route = new VendorRoute($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
        $route_name = !empty($routeName) ? $routeName : self::routeName();

        $_route = array(
            self::ROUTES_KEY_ROUTE => $route,
            self::ROUTES_KEY_ROUTE_NAME => $route_name
        );

        self::$routes[$route_name] = $_route;
        return $_route;
    }

    private static function routeName()
    {
        self::$route_name_id++;
        return "ROUTE_ID:" . self::$route_name_id;
    }

    public static function add($path, $controllerFunc, $methods = array(), array $middleware = array(), $prefix = '', $routeName = '', $host = '', $schemes = array())
    {
        if (is_array($path)) {
            foreach ($path as $r) {
                $path = $r['path'];
                $controllerFunc = $r['controller'];
                $methods = isset($r['methods']) ? $r['methods'] : $methods;
                $prefix = isset($r['prefix']) ? $r['prefix'] : $prefix;
                $routeName = isset($r['name']) ? $r['name'] : $routeName;
                $host = isset($r['host']) ? $r['host'] : $host;
                $schemes = isset($r['schemes']) ? $r['schemes'] : $schemes;
                self::add($path, $controllerFunc, $methods, $prefix, $routeName, $host, $schemes);
            }
        }

        return self::addRoute(
            $path,
            array(
                'controllerFunc' => $controllerFunc,
                'prefix' => $prefix,
                'name' => $routeName,
                'middleware' => $middleware
            ),
            array(),
            array(),
            $host,
            $schemes,
            $methods
        );
    }

    public static function get($path, $controllerFunc, array $middleware = array(), $prefix = '', $routeName = '', $host = '', $schemes = array())
    {
        if (is_array($path)) {
            foreach ($path as &$r) {
                $r['methods'] = "get";
                unset($r);
            }
        }

        return self::add($path, $controllerFunc, "get", $middleware, $prefix, $routeName, $host, $schemes);
    }

    public static function post($path, $controllerFunc, array $middleware = array(), $prefix = '', $routeName = '', $host = '', $schemes = array())
    {
        if (is_array($path)) {
            foreach ($path as &$r) {
                $r['methods'] = "post";
                unset($r);
            }
        }

        return self::add($path, $controllerFunc, "post", $middleware, $prefix, $routeName, $host, $schemes);
    }

    public static function group(array $attributes, \Closure $callable)
    {
        self::$groupAttributesKey++;
        self::$groupAttributes[self::$groupAttributesKey] = $attributes;
        call_user_func($callable);
        unset(self::$groupAttributes[self::$groupAttributesKey]);
    }

    private static function addRoutesToRouteCollection()
    {
        if (!(self::$routeCollection instanceof RouteCollection)) {
            self::$routeCollection = new RouteCollection();
        }

        foreach (self::$routes as $k => $route) {
            $VendorRoute = $route[self::ROUTES_KEY_ROUTE];
            $route_name = $route[self::ROUTES_KEY_ROUTE_NAME];

            self::$routeCollection->add($route_name, $VendorRoute);
        }
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
     * @return VendorRoute
     */
    public static function getRoute($routeName)
    {
        return self::$routes[$routeName]['route'];
    }

    public static function work()
    {
        self::addRoutesToRouteCollection();

        $context = new RequestContext();
        $context->fromRequest(Request::request());

        $matcher = new UrlMatcher(self::$routeCollection, $context);
        $parameters = $matcher->matchRequest(Request::request());

        return $parameters;
    }
}
