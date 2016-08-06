<?php
namespace Toda\Hash;

class Hash{
    public static function make($string){
        return password_hash($string, PASSWORD_BCRYPT);
    }

    public static function verify($string, $hash){
        return password_verify($string, $hash);
    }

    public static function encrypt($pure_string) {
        return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_256,  page('ENCRYPTION_KEY'), $pure_string, MCRYPT_MODE_ECB));
    }

    public static function decrypt($encrypted_string) {
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, page('ENCRYPTION_KEY'), hex2bin($encrypted_string), MCRYPT_MODE_ECB);
    }
}

