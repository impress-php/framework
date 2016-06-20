<?php
namespace Impress\Framework\Http\Session;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session as VendorSession;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class Session extends VendorSession
{
    /**
     * Session constructor.
     * @param String $driver
     * @param array $options
     * @param SessionStorageInterface|null $storage
     * @param AttributeBagInterface|null $attributes
     * @param FlashBagInterface|null $flashes
     */
    public function __construct($driver, array $options = array(), SessionStorageInterface $storage = null, AttributeBagInterface $attributes = null, FlashBagInterface $flashes = null)
    {
        switch ($driver = strtolower($driver)) {
            case 'redis':
            case 'predis':
            case 'memcache':
            case 'memcached':
            case 'mongodb':
            case 'mongo':
            case 'pdo':
            case 'file':
            default:
                $handler = call_user_func([$this, "{$driver}SessionHandler"]);
                break;
        }

        $options = $options ?: config(getenv("SESSION_OPTIONS"));
        $storage = $storage ?: new NativeSessionStorage($options, $handler);
        $attributes = $attributes ?: new AttributeBag("_ips_attributes");
        parent::__construct($storage, $attributes, $flashes);
    }

    private function fileSessionHandler()
    {
        $handler = new NativeFileSessionHandler(SESSIONS_DIR);
        return $handler;
    }
}
