<?php
namespace Toda\Custom;

class Flash extends \Phalcon\Flash\Session
{
    /**
     * This method is called on any attempt to compile a function call
     */
    private $custom = '%s';

    public function setCustom($str){
        $this->custom = $str;
    }


    public function message($type, $message)
    {
        parent::message($type, sprintf($this->custom, $message));
    }
}