<?php
namespace Impress\Framework\Http\Middleware;

use Impress\Framework\Http\Controller;

abstract class Middleware extends Controller implements MiddlewareInterface
{
    abstract public function handle();
}
