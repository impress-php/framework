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
        $controllerFunc = $parameters['controllerFunc'];

        if (is_callable($controllerFunc)) {
            $return = call_user_func($controllerFunc);
            $this->setResponseContent($return);
        } else {
            $atPos = strpos($controllerFunc, "@");
            $className = substr($controllerFunc, 0, $atPos);
            $methodName = substr($controllerFunc, $atPos + 1);

            $calssPosition = "\\App\\Http\\Controllers\\" . $className;
            $class = new $calssPosition();
            $return = call_user_func_array(array($class, $methodName), array());
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
