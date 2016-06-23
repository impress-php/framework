<?php
namespace Impress\Framework\Http\Session;

use Impress\Framework\Database\Memcached;
use Impress\Framework\Database\Redis;
use Impress\Framework\Http\Session\Storage\Handler\RedisSessionHandler;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session as VendorSession;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class Session extends VendorSession
{
    /**
     * Session constructor.
     * @param String $driver
     * @param array $optionsHandler
     * prefix
     * expiretime
     *
     * @param array $optionsStorage
     *
     * List of options for $options array with their defaults.
     *
     * @see http://php.net/session.configuration for options
     * but we omit 'session.' from the beginning of the keys for convenience.
     *
     * ("auto_start", is not supported as it tells PHP to start a session before
     * PHP starts to execute user-land code. Setting during runtime has no effect).
     *
     * cache_limiter, "" (use "0" to prevent headers from being sent entirely).
     * cookie_domain, ""
     * cookie_httponly, ""
     * cookie_lifetime, "0"
     * cookie_path, "/"
     * cookie_secure, ""
     * entropy_file, ""
     * entropy_length, "0"
     * gc_divisor, "100"
     * gc_maxlifetime, "1440"
     * gc_probability, "1"
     * hash_bits_per_character, "4"
     * hash_function, "0"
     * name, "PHPSESSID"
     * referer_check, ""
     * serialize_handler, "php"
     * use_cookies, "1"
     * use_only_cookies, "1"
     * use_trans_sid, "0"
     * upload_progress.enabled, "1"
     * upload_progress.cleanup, "1"
     * upload_progress.prefix, "upload_progress_"
     * upload_progress.name, "PHP_SESSION_UPLOAD_PROGRESS"
     * upload_progress.freq, "1%"
     * upload_progress.min-freq, "1"
     * url_rewriter.tags, "a=href,area=href,frame=src,form=,fieldset="
     *
     * @param SessionStorageInterface|null $storage
     * @param AttributeBagInterface|null $attributes
     * @param FlashBagInterface|null $flashes
     */
    public function __construct($driver, array $optionsHandler = array(), array $optionsStorage = array(), SessionStorageInterface $storage = null, AttributeBagInterface $attributes = null, FlashBagInterface $flashes = null)
    {
        $optionsHandler = $optionsHandler ?: [
            'prefix' => env("SESSION_ID_PREFIX", 'sid_'),
            'expiretime' => env("SESSION_DEFAULT_EXPIRE", 86400)
        ];
        switch ($driver = strtolower($driver)) {
            case 'redis':
            case 'predis':
            case 'memcache':
            case 'memcached':
            case 'mongodb':
            case 'mongo':
            case 'pdo':
            case 'file':
                $handler = call_user_func_array([$this, "{$driver}SessionHandler"], [$optionsHandler]);
                break;
            default:
                throw new \RuntimeException("The session driver not support.");
                break;
        }

        $optionsStorage = array_merge(config(getenv("SESSION_OPTIONS")), $optionsStorage);
        $storage = $storage ?: new NativeSessionStorage($optionsStorage, $handler);
        $attributes = $attributes ?: new AttributeBag("_ips_attributes");
        parent::__construct($storage, $attributes, $flashes);
    }

    private function fileSessionHandler($optionsHandler)
    {
        $handler = new NativeFileSessionHandler(SESSIONS_DIR);
        return $handler;
    }

    private function memcachedSessionHandler($optionsHandler)
    {
        $mc = new Memcached();
        $mc->connect();
        $handler = new MemcachedSessionHandler($mc, $optionsHandler);
        return $handler;
    }

    private function redisSessionHandler($optionsHandler)
    {
        $redis = new Redis();
        $redis->connect();
        $handler = new RedisSessionHandler($redis, $optionsHandler);
        return $handler;
    }
}
