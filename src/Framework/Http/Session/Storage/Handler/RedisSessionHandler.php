<?php
namespace Impress\Framework\Http\Session\Storage\Handler;

class RedisSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var \Redis Memcache driver.
     */
    protected $redis;

    /**
     * @var string Key prefix for shared environments.
     */
    private $prefix;

    /**
     * @var int Time to live in seconds
     */
    private $ttl;

    public function __construct(\Redis $redis, array $options)
    {
        if ($diff = array_diff(array_keys($options), array('prefix', 'expiretime'))) {
            throw new \InvalidArgumentException(sprintf(
                'The following options are not supported "%s"', implode(', ', $diff)
            ));
        }

        $this->redis = $redis;
        $this->ttl = isset($options['expiretime']) ? (int)$options['expiretime'] : 86400;
        $this->prefix = isset($options['prefix']) ? $options['prefix'] : 'ipss_';
    }

    /**
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function close()
    {
        $this->redis->close();
    }

    /**
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $session_id The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function destroy($session_id)
    {
        return $this->redis->del($this->prefix . $session_id);
    }

    /**
     * Cleanup old sessions
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function gc($maxlifetime)
    {
        // not required here because redis will auto expire the records anyhow.
        return true;
    }

    /**
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $session_id The session id.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function open($save_path, $session_id)
    {
        return true;
    }


    /**
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function read($session_id)
    {
        $session_id = $this->prefix . $session_id;
        $this->redis->expire($session_id, $this->ttl);
        return $this->redis->get($session_id);
    }

    /**
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id The session id.
     * @param string $session_data <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     */
    public function write($session_id, $session_data)
    {
        $session_id = $this->prefix . $session_id;
        return $this->redis->set($session_id, $session_data, $this->ttl);
    }
}