<?php
namespace Toda\Validation;


class Validate
{
    public static function required($name, $value, $param)
    {

        return is_null($value) || $value == '' ? sprintf(lang('validate', 'required'), $name) : '';
    }

    public static function email($name, $value, $param)
    {
        return empty($value) || preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', $value) ? '' : sprintf(lang('validate', 'email'), $name);
    }

    public static function unique($name, $value, $param)
    {
        $params = explode(',', $param);

        $message = '';
        if (!empty($value)) {

            $query = $params[0]::where($name, $value);

            if (!empty($params[1])) {
                $query->andWhere('id', '!=', $params[1]);
            }

            if ($query->first()) {
                $message = sprintf(lang('validate', 'unique'), $name);
            }
        }
        return $message;
    }

    public static function number($name, $value, $param)
    {
        return empty($value) || preg_match('/^[-]?[0-9]*\.?[0-9]+$/', $value) ? '' : sprintf(lang('validate', 'number'), $name);
    }

    public static function regex($name, $value, $param)
    {

        return empty($value) || preg_match($param, $value) ? '' : sprintf(lang('validate', 'regex'), $name);
    }

    public static function min($name, $value, $param)
    {
        return empty($value) || (strlen($value) >= $param) ? '' : sprintf(lang('validate', 'min'), $name, $param);
    }

    public static function max($name, $value, $param)
    {
        return empty($value) || (strlen($value) <= $param) ? '' : sprintf(lang('validate', 'max'), $name, $param);
    }

    public static function confirmed($name, $value, $param)
    {
        return empty($value) || ($value === $param) ? '' : sprintf(lang('confirmed', 'email'), $name);
    }
}