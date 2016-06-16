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
    private static $route_name_id = 0;

    private static function addRoute($path, array $defaults = array(), array $requirements = array(), array $options = array(), $host = '', $schemes = array(), $methods = array(), $condition = '')
    {
        $routeName = isset($defaults['name']) ? $defaults['name'] : "";
        $prefix = isset($defaults['prefix']) ? $defaults['prefix'] : "";

        if (isset(self::$groupAttributes['prefix'])) {
            $prefix = self::$groupAttributes['prefix'] . $prefix;
        }

        if (isset(self::$groupAttributes['middleware'])) {
            if (!isset($defaults['middleware'])) {
                $defaults['middleware'] = array();
            }
            $defaults['middleware'] = array_merge(self::$groupAttributes['middleware'], $defaults['middleware']);
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

    public static function add($path, $controllerFunc, $methods = array(), $prefix = '', $routeName = '', $host = '', $schemes = array())
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
                'name' => $routeName
            ),
            array(),
            array(),
            $host,
            $schemes,
            $methods
        );
    }

    public static function get($path, $controllerFunc, $prefix = '', $routeName = '', $host = '', $schemes = array())
    {
        if (is_array($path)) {
            foreach ($path as &$r) {
                $r['methods'] = "get";
                unset($r);
            }
        }

        return self::add($path, $controllerFunc, "get", $prefix, $routeName, $host, $schemes);
    }

    public static function post($path, $controllerFunc, $prefix = '', $routeName = '', $host = '', $schemes = array())
    {
        if (is_array($path)) {
            foreach ($path as &$r) {
                $r['methods'] = "post";
                unset($r);
            }
        }

        return self::add($path, $controllerFunc, "post", $prefix, $routeName, $host, $schemes);
    }

    public static function group(array $attributes, \Closure $callable)
    {
        self::$groupAttributes = $attributes;
        call_user_func($callable);
        self::$groupAttributes = array();
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
