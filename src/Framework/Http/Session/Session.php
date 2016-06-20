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
                $handler = call_user_func([$this, "{$driver}SessionHandler"]);
                break;
            default:
                throw new \Exception("The session driver not support.");
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
