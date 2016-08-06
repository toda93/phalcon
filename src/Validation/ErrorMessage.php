<?php
namespace Toda\Validation;

class ErrorMessage {
    private $messages;
    public function __construct($messages = []){
        $this->messages = $messages;
    }

    public function all(){
        return $this->messages;
    }

    public function __get($key)
    {
        return $this->messages[$key];
    }

    public function __set($key, $value)
    {
        $this->messages[$key] = $value;
    }

    function __isset($key)
    {
        return isset($this->messages[$key]);
    }


}