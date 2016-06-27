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
     * @param array|String $options
     * ***********************************************************************************
     * #------------- [options] -------------#
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
     *
     * #------------- [handler] -------------#
     * prefix, "sid_"
     * expiretime, 86400
     * ***********************************************************************************
     *
     * @param boolean $createNew Create new session id, destroy old session from cookie and storage.
     * @param null|String $driver
     * @param null|string $driverConfig
     */
    public function __construct($options, $createNew = false, $driver = null, $driverConfig = null)
    {
        is_string($options) && $options = config($options);

        $options = $options ?: config(env("SESSION_DEFAULT_OPTIONS"));

        if (!$options || !$options['handler'] || !$options['options']) {
            throw new \RuntimeException("Invalid options: " . var_export($options, true));
        }

        $driver = $driver ?: env("SESSION_DRIVER", 'file');
        $driverConfig = $driverConfig ?: env("SESSION_DRIVER_CONFIG");

        $optionsHandler = $options['handler'];

        switch ($driver = strtolower($driver)) {
            case 'redis':
            case 'predis':
            case 'memcache':
            case 'memcached':
            case 'mongodb':
            case 'mongo':
            case 'pdo':
            case 'file':
                $handler = call_user_func_array([$this, "{$driver}SessionHandler"], [$driverConfig, $optionsHandler]);
                break;
            default:
                throw new \RuntimeException("The session driver not support.");
                break;
        }

        $optionsStorage = $options['options'];
        $storage = new NativeSessionStorage($optionsStorage, $handler);
        $attributes = new AttributeBag("_ips_attributes");

        if ($createNew) {
            $session_name = isset($options['options']['name']) ? $options['options']['name'] : "PHPSESSID";
            if (isset($_COOKIE[$session_name])) {
                $old_session_id = $_COOKIE[$session_name];
                $_COOKIE[$session_name] = null;
                @$storage->getSaveHandler()->destroy($old_session_id);
            }
        }

        parent::__construct($storage, $attributes);
    }

    private function fileSessionHandler($driverConfig, $optionsHandler)
    {
        $handler = new NativeFileSessionHandler(SESSIONS_DIR);
        return $handler;
    }

    private function memcachedSessionHandler($driverConfig, $optionsHandler)
    {
        $mc = new Memcached();
        $mc->connect($driverConfig);
        $handler = new MemcachedSessionHandler($mc, $optionsHandler);
        return $handler;
    }

    private function redisSessionHandler($driverConfig, $optionsHandler)
    {
        $redis = new Redis();
        $redis->connect($driverConfig);
        $handler = new RedisSessionHandler($redis, $optionsHandler);
        return $handler;
    }
}
