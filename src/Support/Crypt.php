<?php
namespace Impress\Support;

use phpseclib\Crypt\AES;
use phpseclib\Crypt\DES;

class Crypt
{
    public static function cryptAES($value, $length = 256, $withIv = false)
    {
        $cryptTypeClass = new AES();
        return self::cryptAESorDES($cryptTypeClass, $value, $length, $withIv);
    }

    public static function decryptAES($value, $length = 256, $withIv = false)
    {
        $cryptTypeClass = new AES();
        return self::decryptAESorDES($cryptTypeClass, $value, $length, $withIv);
    }

    public static function cryptDES($value, $length = 256, $withIv = false)
    {
        $cryptTypeClass = new DES();
        return self::cryptAESorDES($cryptTypeClass, $value, $length, $withIv);
    }

    public static function decryptDES($value, $length = 256, $withIv = false)
    {
        $cryptTypeClass = new DES();
        return self::decryptAESorDES($cryptTypeClass, $value, $length, $withIv);
    }

    public static function cryptAESorDES(\phpseclib\Crypt\Base $cryptTypeClass, $value, $length = 256, $withIv = false)
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

    public static function decryptAESorDES(\phpseclib\Crypt\Base $cryptTypeClass, $string, $length = 256, $withIv = false)
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