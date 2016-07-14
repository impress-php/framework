<?php
namespace Impress\Framework\Http\Middleware;

use Impress\Framework\Http\Controller;

abstract class Middleware extends Controller implements MiddlewareInterface
{
    public function __construct(array $routeParameters = [])
    {
        parent::__construct($routeParameters);
    }

    abstract public function handle();
}
