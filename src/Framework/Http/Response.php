<?php
namespace Impress\Framework\Http;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response as VendorResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Response extends VendorResponse
{
    public function raw($content = "", $statusCode = 200, array $headers = array())
    {
        $this->setContent($content);
        $this->setStatusCode($statusCode);
        $this->headers->add($headers);
    }

    public function view($name, array $data = array(), $statusCode = 200, array $headers = array(), $engine = View::ENGINE_AUTO)
    {
        $content = View::make($name, $data, $engine);
        $this->raw($content, $statusCode, $headers);
        return $this;
    }

    public function json(array $data, $statusCode = 200, array $headers = array(
        "Content-Type" => "application/json"
    ))
    {
        $content = json_encode($data);
        $this->raw($content, $statusCode, $headers);
        return $this;
    }

    /**
     * setCookie.
     *
     * @param string $name The name of the cookie
     * @param string $value The value of the cookie
     * @param int|string|\DateTime|\DateTimeInterface $expire The time the cookie expires
     * @param string $path The path on the server in which the cookie will be available on
     * @param string $domain The domain that the cookie is available to
     * @param bool $secure Whether the cookie should only be transmitted over a secure HTTPS connection from the client
     * @param bool $httpOnly Whether the cookie will be made accessible only through the HTTP protocol
     * @param bool $raw Whether the cookie value should be sent with no url encoding
     *
     * @throws \InvalidArgumentException
     */
    public function setCookie($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true, $raw = false)
    {
        $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw);
        $this->headers->setCookie($cookie);
    }

    /**
     * Returns an array with all cookies.
     *
     * @param string $format
     *
     * @return array
     *
     * @throws \InvalidArgumentException When the $format is invalid
     */
    public function getCookies($format = ResponseHeaderBag::COOKIES_FLAT)
    {
        $this->headers->getCookies($format);
    }

    /**
     * Clears a cookie in the browser.
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public function clearCookie($name, $path = '/', $domain = null, $secure = false, $httpOnly = true)
    {
        $this->headers->clearCookie($name, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Removes a cookie from the array, but does not unset it in the browser.
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     */
    public function removeCookie($name, $path = '/', $domain = null)
    {
        $this->headers->removeCookie($name, $path, $domain);
    }
}
