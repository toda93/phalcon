<?php
namespace Toda\Validation;

class BootstrapFlash extends \Phalcon\Flash\Session
{
    public function message($type, $message)
    {
        $message = '<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' . $message;
        parent::message($type, $message);
    }
}