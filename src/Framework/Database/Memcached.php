<?php
namespace Impress\Framework\Database;

use \Memcached as MC;

class Memcached extends MC
{
    public function connect($configItem = 'default')
    {
        $this->setOptions(config("database.memcached.{$configItem}.options"));
        $this->addServers(config("database.memcached.{$configItem}.servers"));
    }
}
