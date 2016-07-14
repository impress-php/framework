<?php
namespace Impress\Framework\Http\Route;

use Symfony\Component\Routing\Route as SymfonyRoute;

class RouteParametersItem
{
    const ROUTE_PARAMETER_PREFIX = '__';
    const ROUTE_PARAMETER_CONTROLLER_KEY = self::ROUTE_PARAMETER_PREFIX . 'controller';
    const ROUTE_PARAMETER_MIDDLEWARE_KEY = self::ROUTE_PARAMETER_PREFIX . 'middleware';

    private $parameters;

    private $ControllerAndMethod;
    private $Controller;
    private $Method;
    private $Middleware;
    private $Arguments;
    private $Route;

    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getControllerAndMethod()
    {
        if (!is_null($this->ControllerAndMethod)) {
            return $this->ControllerAndMethod;
        }

        if (is_null($this->parameters)) {
            return null;
        }

        $controller = isset($this->parameters[self::ROUTE_PARAMETER_CONTROLLER_KEY]) ?
            $this->parameters[self::ROUTE_PARAMETER_CONTROLLER_KEY] : null;
        if (!$controller) {
            return $this->ControllerAndMethod = [];
        }

        if (is_callable($controller)) {
            return $this->ControllerAndMethod = $controller;
        }

        $atPos = strpos($controller, "@");
        $className = substr($controller, 0, $atPos);
        $methodName = substr($controller, $atPos + 1);

        return $this->ControllerAndMethod = [
            $className,
            $methodName
        ];
    }

    public function getController()
    {
        if (!is_null($this->Controller)) {
            return $this->Controller;
        }
        if (is_null($this->getControllerAndMethod())) {
            return null;
        }
        return $this->Controller = $this->getControllerAndMethod()[0];
    }

    public function getMethod()
    {
        if (!is_null($this->Method)) {
            return $this->Method;
        }
        if (is_null($this->getControllerAndMethod())) {
            return null;
        }
        return $this->Method = $this->getControllerAndMethod()[1];
    }

    public function getMiddleware()
    {
        if (!is_null($this->Middleware)) {
            return $this->Middleware;
        }
        if (is_null($this->parameters)) {
            return null;
        }
        return $this->Middleware = isset($this->parameters[self::ROUTE_PARAMETER_MIDDLEWARE_KEY]) ?
            $this->parameters[self::ROUTE_PARAMETER_MIDDLEWARE_KEY] : null;
    }

    public function getArguments()
    {
        if (!is_null($this->Arguments)) {
            return $this->Arguments;
        }
        if (is_null($this->parameters)) {
            return null;
        }

        $args = [];
        foreach ($this->parameters as $k => $v) {
            if (
                substr($k, 0, count(self::ROUTE_PARAMETER_PREFIX) + 1) !== self::ROUTE_PARAMETER_PREFIX
                /**
                 * remove _route default @see UrlMatcher::getAttributes
                 */
                && $k !== '_route'
                /**
                 * support Routes file =? to Controller @see Impress\Framework\Http\Route\Route::makeRouteMatchOptions
                 */
                && !is_null($v)
            ) {
                $args[$k] = $v;
            }
        }
        return $this->Arguments = $args;
    }

    /**
     * @return SymfonyRoute
     */
    public function getRoute()
    {
        if (!is_null($this->Route)) {
            return $this->Route;
        }
        if (is_null($this->parameters)) {
            return null;
        }

        if (isset($this->parameters['_route'])) {
            return $this->Route = RouteMatch::getRoute($this->parameters['_route']);
        }

        return null;
    }
}
