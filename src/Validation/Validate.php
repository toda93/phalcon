<?php
namespace Toda\Validation;


class Validate
{

    protected static $message = [
        'required' => 'The %s field is required.',
        'email' => 'The %s must be a valid email address.',
        'unique' => 'The %s has already been taken.',
        'regex' => 'The %s not match.',
        'number' => 'The %s is not a number',
        'min' => 'The %s must be at least %s.',
        'max'=> 'The %s may not be greater than %s.',
        'confirmed' => 'The %s confirmation does not match.',
    ];

    public static function required($name, $value, $param)
    {
        return !empty($value) ? '' : sprintf(self::$message['required'], $name);
    }

    public static function email($name, $value, $param)
    {
        return empty($value) || preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', $value) ? '' : sprintf(self::$message['email'], $name);
    }

    public static function unique($name, $value, $param){
        $message = '';
        if(!empty($value)){
            if($param::where($name, $value)->first()){
                $message = sprintf(self::$message['unique'], $name);
            }
        }
        return $message;
    }

    public static function number($name, $value, $param){
        return empty($value) || !is_nan($value) ? '' : sprintf(self::$message['number'], $name);;
    }

    public static function regex($name, $value, $param){
        return empty($value) || preg_match($param, $value) ? '' : sprintf(self::$message['regex'], $name);
    }

    public static function min($name, $value, $param){
        return empty($value) || (strlen($value) >= $param) ? '' : sprintf(self::$message['min'], $name, $param);
    }

    public static function max($name, $value, $param){
        return empty($value) || (strlen($value) <= $param) ? '' : sprintf(self::$message['max'], $name, $param);
    }

    public static function confirmed($name, $value, $param){
        return empty($value) || ($value === $param) ? '' : sprintf(self::$message['confirmed'], $name);
    }
}