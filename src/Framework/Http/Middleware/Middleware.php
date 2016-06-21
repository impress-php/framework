<?php
namespace Impress\Framework\Http\Middleware;

use Impress\Framework\Http\Request;

abstract class Middleware
{
    abstract public function handle(Request $request);
}
