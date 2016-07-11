<?php
namespace Impress\Framework\Http\Route;

class Route
{
    private static function makeRouteMatchOptions($path, $controller, $method, $options)
    {
        $_options = [
            RouteMatch::ROUTE_PARAMETER_PREFIX . 'path' => $path,
            RouteMatch::ROUTE_PARAMETER_PREFIX . 'controller' => $controller
        ];
        foreach ($options as $k => $v) {
            $_options[RouteMatch::ROUTE_PARAMETER_PREFIX . $k] = $v;
        }
        unset($options);

        if ($method == 'any' || $method == 'all') {
            $method = [];
        }

        $_options[RouteMatch::ROUTE_PARAMETER_PREFIX . 'methods'] = $method;
        return $_options;
    }

    public static function __callStatic($method, $arguments)
    {
        $path = $arguments[0];
        $controller = $arguments[1];
        $options = isset($arguments[2]) ? $arguments[2] : [];

        return RouteMatch::addRoute(self::makeRouteMatchOptions($path, $controller, $method, $options));
    }

    public static function controller($controllerClassName, array $options = array())
    {
        $controllerClassName = trim($controllerClassName, "\\");
        $class = "\\App\\Http\\Controllers\\" . $controllerClassName;
        $classMethods = get_class_methods($class);
        if (is_null($classMethods)) return null;

        $diffClassMethods = array_merge(
            (get_class_methods('\Impress\Framework\Http\Controller') ?: []),
            (get_class_methods(get_parent_class($classMethods)) ?: []),
            ['__construct']
        );
        $classMethods = array_unique(array_diff($classMethods, $diffClassMethods));
        if (!$classMethods) return null;

        $routes = array();
        foreach ($classMethods as $m) {
            if (!preg_match('/([a-z])/', substr($m, 0, 1))) {
                continue;
            }
            $methodStr = trim(strtolower(preg_replace('/([A-Z])/', '_$1', $m)), '_');
            if (($pos = strpos($methodStr, "_")) === false) {
                continue;
            }
            $method = strtolower(substr($methodStr, 0, $pos));
            $path = substr($methodStr, $pos + 1);
            $path = rtrim($path, "index");
            $controller = "{$controllerClassName}@{$m}";

            $routes[] = RouteMatch::addRoute(self::makeRouteMatchOptions($path, $controller, $method, $options));
        }
        return $routes;
    }
}
