<?php
namespace Impress\Framework\Http;

use Impress\Framework\Http\Route\RouteMatch;
use Impress\Framework\Http\Middleware\MiddlewareMatch;

class Bootstrap
{
    private static $routesFile;
    private $responseContent;

    public function __construct($routesFile = null)
    {
        if (is_file($routesFile)) {
            self::$routesFile = $routesFile;
        } else {
            self::$routesFile = app_path('Http' . DIRECTORY_SEPARATOR . 'Routes.php');
        }

        $this->httpWork();
    }

    private function httpWork()
    {
        $RouteMatch = new RouteMatch(self::$routesFile);

        $parameters = $RouteMatch->work();
        $routeController = RouteMatch::getController($parameters);
        $routeArguments = RouteMatch::getArguments($parameters);

        if (is_callable($routeController)) {
            $return = call_user_func_array($routeController, $routeArguments);
        } else {
            $className = $routeController[0];
            $methodName = $routeController[1];

            $calssPosition = "\\App\\Http\\Controllers\\" . $className;
            $class = new $calssPosition($parameters);

            // middleware work
            $return = MiddlewareMatch::work($parameters);

            is_bool($return) && $return = call_user_func_array([$class, $methodName], $routeArguments);
        }

        $this->setResponseContent($return);
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
