<?php
namespace Impress\Framework\Http\Route;

class Route
{
    private static $groupOptionsKey = 0;
    private static $groupOptions = array();

    private static function makeRouteMatchOptions($path, $controller, $method, $options)
    {
        $path = str_replace(" ", "", $path);
        is_null($options) && $options = [];

        // method
        if ($method == 'any' || $method == 'all') {
            $method = [];
        }
        $options['methods'] = $method;

        // middleware
        if (isset($options['middleware'])) {
            if (!is_array($options['middleware'])) {
                $options['middleware'] = [$options['middleware']];
            }
        }

        // group:
        if (!empty(self::$groupOptions)) {
            $prefix_arr = array();
            foreach (self::$groupOptions as $ga) {
                if (isset($ga['prefix'])) {
                    array_push($prefix_arr, $ga['prefix']);
                }

                if (isset($ga['middleware'])) {
                    $options['middleware'] = array_merge($ga['middleware'], isset($options['middleware']) ? $options['middleware'] : []);
                }
            }
            $path = implode("", $prefix_arr) . (!empty($path) ? $path : '');
        }

        // defaults & requirements
        preg_match_all("/{(.[^{^}]*)}/", $path, $matches);
        if (isset($matches[1])) {
            foreach ($matches[1] as $m) {
                $pos = strpos($m, "=");
                $k = substr($m, 0, $pos);
                $v = substr($m, $pos + 1);
                /**
                 * support Routes file =? to Controller @see Impress\Framework\Http\Route\RouteParametersItem::getArguments
                 */
                $options['defaults'][$k] = ($v == "?" ? null : $v);
            }
        }
        $path = preg_replace("/{(.[^{^}]*)=(.[^{^}]*)}/", "{\$1}", $path);
        if (isset($options['where'])) {
            $options['requirements'] = $options['where'];
            unset($options['where']);
        }

        // assemble options
        $options['path'] = $path;
        $options['controller'] = $controller;
        isset($options['middleware']) && $options['middleware'] = array_unique($options['middleware']);

        // // assemble _options
        $_options = [];
        foreach ($options as $k => $v) {
            $_options[RouteParametersItem::ROUTE_PARAMETER_PREFIX . $k] = $v;
        }
        unset($options);

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
            (get_class_methods(get_parent_class($class)) ?: []),
            ['__construct', '__destruct', '__clone', '__get', '__set', '__call', '__callStatic', '__sleep', '__wakeup', '__clone']
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

            //name
            if (isset($options['name']) || isset($options['as'])) {
                if (isset($options['as'])) {
                    $options['name'] = $options['as'];
                }
                $options['name'] = $options['name'] . "@{$m}";
            }

            $routes[] = RouteMatch::addRoute(self::makeRouteMatchOptions($path, $controller, $method, $options));
        }
        return $routes;
    }

    public static function group(array $options, \Closure $callable)
    {
        self::$groupOptionsKey++;
        self::$groupOptions[self::$groupOptionsKey] = $options;
        call_user_func($callable);
        unset(self::$groupOptions[self::$groupOptionsKey]);
    }
}
