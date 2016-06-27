<?php
namespace Impress\Framework\Http;

use Symfony\Component\HttpFoundation\Cookie as VendorCookie;
use Impress\Support\Crypt;

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
                    $value = Crypt::cryptAES($value, self::$cryptLength);
                    break;
                case "DES":
                    $value = Crypt::cryptDES($value, self::$cryptLength);
                    break;
            }
        }
        return $value;
    }

    public static function decryptCookieValue($value)
    {
        if (!is_null(self::$crypt)) {
            switch (self::$cryptType) {
                case "AES":
                    $value = Crypt::decryptAES($value, self::$cryptLength);
                    break;
                case "DES":
                    $value = Crypt::decryptDES($value, self::$cryptLength);
                    break;
            }
        }
        return $value;
    }
}
