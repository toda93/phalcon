<?php
namespace Toda\Validation;

class OldValue {
    private $values;
    public function __construct($values = []){
        $this->values = $values;
    }
    public function all(){
        return $this->values;
    }

    public function __get($key)
    {
        return empty($this->values[$key]) ? '' : $this->values[$key];
    }

    public function __set($key, $value)
    {
        $this->values[$key] = $value;
    }

    function __isset($key)
    {
        return isset($this->values[$key]);
    }


}