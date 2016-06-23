<?php
namespace Impress\Framework\Database;

use \Memcached as MC;

class Memcached extends MC
{
    public function connect($configItem = 'database.memcached.default')
    {
        $options = config("{$configItem}.options", []);
        !empty($options) && $this->setOptions($options);

        $servers = config("{$configItem}.servers", []);
        !empty($servers) && $this->addServers($servers);
    }
}
