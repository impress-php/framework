<?php
namespace Impress\Framework\Database;

use \Redis as R;

class Redis extends R
{
    public function connect($configItem = 'default')
    {
        $host = config("database.redis.{$configItem}.host", '127.0.0.1');
        $port = config("database.redis.{$configItem}.port", 6379);
        $timeout = config("database.redis.{$configItem}.timeout", 0);

        parent::connect($host, $port, $timeout);

        $options = config("database.redis.{$configItem}.options");
        (is_array($options) && !empty($options)) && $this->setOptions($options);
        $auth = config("database.redis.{$configItem}.auth");
        $auth && parent::auth($auth);
    }

    public function setOptions($options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }
}
