<?php
namespace Impress\Framework\Database;

use \Redis as R;

class Redis extends R
{
    public function connect($configItem = 'database.redis.default')
    {
        $config = config($configItem);
        if (!$config) {
            return;
        }

        $host = (isset($config['host'])) ? $config['host'] : '127.0.0.1';
        $port = (isset($config['port'])) ? $config['port'] : 6379;
        $timeout = (isset($config['timeout'])) ? $config['timeout'] : 0;

        parent::connect($host, $port, $timeout);

        $options = $config['options'];
        (is_array($options) && !empty($options)) && $this->setOptions($options);

        $auth = $config['auth'];
        $auth && parent::auth($auth);
    }

    public function setOptions($options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }
}
