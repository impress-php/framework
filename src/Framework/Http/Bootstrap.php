<?php
namespace Impress\Framework\Http;

class Bootstrap
{
    private static $routesFile;
    private $responseContent;

    public function __construct($routesFile = null)
    {
        if (is_file($routesFile)) {
            self::$routesFile = $routesFile;
        } else {
            self::$routesFile = ROUTES_FILE;
        }

        $this->httpWork();
    }

    public static function getRoutesFile()
    {
        return self::$routesFile;
    }

    private function httpWork()
    {
        if (getenv("ROUTES_NO_CACHE")) {
            if (is_file(self::$routesFile)) {
                require_once(self::$routesFile);
            }
        } else {
            RouteCache::makeRoutes();
        }

        $parameters = Route::work();
        $routeControllerFunc = $parameters['controllerFunc'];
        $routeMiddleware = $parameters['middleware'];

        if (is_callable($routeControllerFunc)) {
            $return = call_user_func($routeControllerFunc);
            $this->setResponseContent($return);
        } else {
            $atPos = strpos($routeControllerFunc, "@");
            $className = substr($routeControllerFunc, 0, $atPos);
            $methodName = substr($routeControllerFunc, $atPos + 1);

            $calssPosition = "\\App\\Http\\Controllers\\" . $className;
            $class = new $calssPosition();

            // Middleware
            if ($routeMiddleware) {
                call_user_func_array([$class, 'middleware'], $routeMiddleware);
            }
            $return = Middleware::handle();

            is_bool($return) && $return = call_user_func_array([$class, $methodName], array());
            $this->setResponseContent($return);
        }
    }

    private function setResponseContent($content)
    {
        $this->responseContent = $content;
    }

    public function getResponseContent()
    {
        return $this->responseContent;
    }

    public function response()
    {
        if (!is_null($this->responseContent)) {
            if ($this->responseContent instanceof Response) {
                $this->responseContent->send();
            } else {
                $response = new Response();
                $response->setContent($this->responseContent);
                $response->setStatusCode(200);
                $response->send();
            }
        }
    }
}
