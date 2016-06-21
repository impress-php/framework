<?php
namespace Impress\Framework\Database;

use \Memcached as MC;

class Memcached extends MC
{
    public function connect()
    {
        $this->setOptions(config("database.memcached.default.options"));
        $this->addServers(config("database.memcached.default.servers"));
    }
}
