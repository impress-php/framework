<?php
namespace Impress\Framework\Http;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session as VendorSession;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class Session extends VendorSession
{
    public function __construct($driver, SessionStorageInterface $storage = null, AttributeBagInterface $attributes = null, FlashBagInterface $flashes = null)
    {
        switch ($driver) {
            case 'redis':
                break;
            case 'predis':
                break;
            case 'memcache':
                break;
            case 'memcached':
                break;
            case 'mongodb':
            case 'mongo':
                break;
            case 'pdo':
                break;
            case 'file':
            default:
                break;
        }
        $storage = $storage ?: new NativeSessionStorage();
        parent::__construct($storage, $attributes, $flashes);
    }
}
