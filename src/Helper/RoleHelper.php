<?php
namespace Toda\Helper;
class RoleHelper
{
    public static function check($key)
    {
        $auth = \Phalcon\DI::getDefault()->getSession()->get('auth');

        return $auth->level == 0 || preg_match("/$key/", $auth->roles);
    }

}