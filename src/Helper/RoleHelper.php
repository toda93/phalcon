<?php
namespace Toda\Helper;
class RoleHelper
{
    public static function check($key)
    {
        $auth = \Phalcon\DI::getDefault()->getSession()->get('auth');
        if ($auth->level != 0) {

            $array_regex = explode('|', $key);

            foreach ($array_regex as $regex) {

                if (preg_match("/$regex/", $auth->roles)) {

                    return true;
                }
            }
            return false;
        }
        return true;
    }

}