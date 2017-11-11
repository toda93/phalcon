<?php
namespace Toda\Hash;

class Hash
{
    public static function make($string)
    {
        return password_hash($string, PASSWORD_BCRYPT);
    }

    public static function verify($string, $hash)
    {
        return password_verify($string, $hash);
    }

    public static function encrypt($pure_string, $key)
    {
        return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $pure_string, MCRYPT_MODE_ECB));
    }

    public static function decrypt($encrypted_string, $key)
    {
        $result = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, hex2bin($encrypted_string), MCRYPT_MODE_ECB);
        return strval(str_replace("\0", "", $result));
    }


    public static function encodeTime($time)
    {
        $time = (string)$time;
        $result = '';
        for ($i = 0; $i < strlen($time); $i++) {
            if ($i % 2) {
                $result .= chr(rand(97, 122));
            }

            $result .= $time[$i];
        }
        return $result;
    }

    public static function decodeTime($hash)
    {
        return preg_replace('/[a-z]+/', '', $hash);
    }
}

