<?php
namespace Impress\Framework\Http;

use phpseclib\Crypt\AES;
use phpseclib\Crypt\DES;
use phpseclib\Crypt\Base;
use Symfony\Component\HttpFoundation\Cookie as VendorCookie;

class Cookie extends VendorCookie
{
    private static $crypt;
    private static $cryptType;
    private static $cryptLength;

    public function __construct($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw)
    {
        self::$crypt = env("COOKIE_CRYPT", null);
        if (!is_null(self::$crypt)) {
            $_crypt = strpos(self::$crypt, ".");
            self::$cryptType = strtoupper(substr(self::$crypt, 0, $_crypt));
            self::$cryptLength = intval(substr(self::$crypt, $_crypt + 1));
        }

        $value = self::cryptValue($value);
        parent::__construct($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw);
    }

    private static function cryptValue($value)
    {
        if (!is_null(self::$crypt)) {
            switch (self::$cryptType) {
                case "AES":
                case "DES":
                    $className = "\\phpseclib\\Crypt\\" . self::$cryptType;
                    $class = new $className;
                    $value = self::cryptAESorDES($class, $value, self::$cryptLength);
            }
        }
        return $value;
    }

    public static function decryptCookieValue($value)
    {
        if (!is_null(self::$crypt)) {
            switch (self::$cryptType) {
                case "AES":
                case "DES":
                    $className = "\\phpseclib\\Crypt\\" . self::$cryptType;
                    $class = new $className;
                    $value = self::decryptAESorDES($class, $value, self::$cryptLength);
            }
        }
        return $value;
    }

    private static function cryptAESorDES(\phpseclib\Crypt\Base $cryptTypeClass, $value, $length = 256, $withIv = false)
    {
        $key = hash('SHA256', env("KEY", md5("impress-php")), true);

        $c = $cryptTypeClass;
        $c->setPreferredEngine(\phpseclib\Crypt\Base::ENGINE_OPENSSL);
        $c->setKeyLength($length);
        $c->setKey($key);

        if ($withIv) {
            $iv = md5($key . microtime(true) . mt_rand());
            for ($i = 0; $i < 32; $i++) {
                $iv[$i] = (boolval(mt_rand(0, 1)) && !is_numeric($iv[$i])) ? strtoupper($iv[$i]) : $iv[$i];
            }
            $c->setIV($iv);
        }

        $value = base64_encode($c->encrypt($value . md5($value . $key)));

        if ($withIv) {
            return $iv . $value;
        } else {
            return $value;
        }
    }

    private static function decryptAESorDES(\phpseclib\Crypt\Base $cryptTypeClass, $string, $length = 256, $withIv = false)
    {
        if ($withIv) {
            $iv = substr($string, 0, 32);
            $string = substr($string, 32);
        }

        $key = hash('SHA256', env("KEY", md5("impress-php")), true);

        $c = $cryptTypeClass;
        $c->setPreferredEngine(\phpseclib\Crypt\Base::ENGINE_OPENSSL);
        $c->setKeyLength($length);
        $c->setKey($key);
        ($withIv) && $c->setIV($iv);
        $string = $c->decrypt(base64_decode($string));

        $value = substr($string, 0, -32);
        $hash = substr($string, -32);
        if ($hash !== md5($value . $key)) {
            return false;
        }
        return $value;
    }
}
