<?php
namespace Impress\Framework\Http;

use Impress\Framework\Http\Session\Session;

class Controller
{
    private static $response;

    /**
     * @param array $query
     * @param array $request
     * @param array $attributes
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param null $content
     * @return Request
     */
    public function request(
        array $query = array(),
        array $request = array(),
        array $attributes = array(),
        array $cookies = array(),
        array $files = array(),
        array $server = array(),
        $content = null
    )
    {
        return Request::request($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * @param Request|null $request
     * @return null|\Impress\Framework\Http\Session\Session
     */
    public function session(Request $request = null)
    {
        $request = $request ?: $this->request();
        return $request->getSession();
    }

    /**
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
     *
     * @param Request $request
     */
    public function session_start(array $optionsStorage = array(), Request $request = null)
    {
        $request = $request ?: $this->request();
        $session = new Session(env("SESSION_DRIVER", 'file'), [], $optionsStorage);
        $request->setSession($session);
        session_start();
    }

    public function clearRequest()
    {
        Request::clearRequest();
    }

    public function response()
    {
        if (!(self::$response instanceof Response)) {
            self::$response = new Response();
        }
        return self::$response;
    }

    public function clearResponse()
    {
        self::$response = null;
    }

    public function middleware($middleware)
    {
        Middleware::addMiddleware($middleware);
    }
}
