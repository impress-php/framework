<?php
namespace Impress\Framework\ORM\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

class EntityManagerMysql
{
    /**
     * @param string $configItem
     * @param EventManager|null $eventManager
     * @return EntityManager
     * @throws \Doctrine\ORM\ORMException
     */
    public static function create($configItem = 'master', EventManager $eventManager = null)
    {
        switch (true) {
            case (is_string($configItem)):
                $config = config("database.mysql." . $configItem);
                $conn = self::createConn($config);
                break;
            case (is_array($configItem)):
            case ($configItem instanceof Connection):
                $conn = $configItem;
                break;
            default:
                throw new \RuntimeException("Invalid argument: " . $configItem);
        }

        return EntityManager::create($conn, Setup::createAnnotationMetadataConfiguration(), $eventManager);
    }

    private static function createConn(array $config)
    {
        return [
            'driver' => 'pdo_mysql',
            'host' => $config['host'],
            'port' => $config['port'],
            'user' => $config['username'],
            'password' => $config['password'],
            'dbname' => $config['database']
        ];
    }
}
