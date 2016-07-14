<?php
namespace Impress\Framework\Http;

use Impress\Framework\Http\Middleware\MiddlewareMatch;
use Impress\Framework\Http\Route\RouteMatch;
use Impress\Framework\Http\Session\Session;

class Controller
{
    private $routeParameters;
    private static $response;

    public function __construct(array $routeParameters = [])
    {
        $this->routeParameters = $routeParameters;
    }

    /**
     * @param array $query
     * @param array $request
     * @param array $attributes
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param null $content
     * @return Request
     */
    public function request(
        array $query = array(),
        array $request = array(),
        array $attributes = array(),
        array $cookies = array(),
        array $files = array(),
        array $server = array(),
        $content = null
    )
    {
        return Request::request($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * @param Request|null $request
     * @return null|\Impress\Framework\Http\Session\Session
     */
    public function session(Request $request = null)
    {
        $request = $request ?: $this->request();
        return $request->getSession();
    }

    public function session_start($options = null, $createNew = false, $driver = null, $driverConfig = null, $request = null)
    {
        $request = $request ?: $this->request();
        $session = new Session($options, $createNew, $driver, $driverConfig);
        $request->setSession($session);
        session_start();
    }

    public function clearRequest()
    {
        Request::clearRequest();
    }

    public function response($content = '', $status = 200, $headers = array())
    {
        if (!(self::$response instanceof Response)) {
            self::$response = new Response($content, $status, $headers);
        }
        return self::$response;
    }

    public function clearResponse()
    {
        self::$response = null;
    }

    public function middleware($middleware, array $options = [])
    {
        MiddlewareMatch::addMiddleware($middleware, $options);
    }

    public function getController()
    {
        return RouteMatch::getController($this->routeParameters);
    }

    public function getArguments()
    {
        return RouteMatch::getArguments($this->routeParameters);
    }

    public function getRoute()
    {
        return RouteMatch::getRouteByParameters($this->routeParameters);
    }
}
