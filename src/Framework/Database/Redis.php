<?php
namespace Impress\Framework\Database;

use \Redis as R;

class Redis extends R
{
    public function connect($host = null, $port = 6379, $timeout = 0.0)
    {
        $setOptionsByDefault = false;
        if (is_null($host)) {
            $host = config("database.redis.default.host") ?: '127.0.0.1';
            $port = config("database.redis.default.port") ?: 6379;
            $timeout = config("database.redis.default.timeout") ?: 0;
            $setOptionsByDefault = true;
        }

        parent::connect($host, $port, $timeout);

        if ($setOptionsByDefault) {
            $options = config("database.redis.default.options");
            is_array($options) && $this->setOptions($options);
            $auth = config("database.redis.default.auth");
            $auth && parent::auth($auth);
        }
    }

    public function setOptions($options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }
}
