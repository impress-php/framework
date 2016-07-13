<?php
namespace Impress\Framework\Http\Middleware;

class MiddlewareItem
{
    private $middleware;
    private $only = [];
    private $except = [];

    public function __construct($middleware, array $options = [])
    {
        if (!is_string($middleware)) {
            throw new \RuntimeException("Only allowed string of type.");
        }
        $this->middleware = $middleware;

        if (isset($options['only'])) {
            $this->only($options['only']);
        }

        if (isset($options['except'])) {
            $this->except($options['except']);
        }
    }

    private function only($functionName)
    {
        if (is_array($functionName)) {
            foreach ($functionName as $f) {
                $this->only($f);
            }
            return $this;
        }

        if (!is_string($functionName)) {
            throw new \RuntimeException("Only allowed string of type.");
        }
        array_push($this->only, $functionName);
        return $this;
    }

    private function except($functionName)
    {
        if (is_array($functionName)) {
            foreach ($functionName as $f) {
                $this->except($f);
            }
            return $this;
        }

        if (!is_string($functionName)) {
            throw new \RuntimeException("Only allowed string of type.");
        }
        array_push($this->except, $functionName);
        return $this;
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }

    public function getOnly()
    {
        return $this->only;
    }

    public function getExcept()
    {
        return $this->except;
    }
}
