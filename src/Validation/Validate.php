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
        return empty($value) || preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/', $value) ? '' : sprintf(lang('validate', 'email'), $value);
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
                $message = sprintf(lang('validate', 'unique'), $value);
            }
        }
        return $message;
    }

    public static function number($name, $value, $param)
    {
        return empty($value) || preg_match('/^[-]?[0-9]*\.?[0-9]+$/', $value) ? '' : sprintf(lang('validate', 'number'), $value);
    }

    public static function regex($name, $value, $param)
    {

        return empty($value) || preg_match($param, $value) ? '' : sprintf(lang('validate', 'regex'), $value);
    }

    public static function min($name, $value, $param)
    {
        return empty($value) || (strlen($value) >= $param) ? '' : sprintf(lang('validate', 'min'), $value, $param);
    }

    public static function max($name, $value, $param)
    {
        return empty($value) || (strlen($value) <= $param) ? '' : sprintf(lang('validate', 'max'), $value, $param);
    }

    public static function confirmed($name, $value, $param)
    {
        return empty($value) || ($value === $param) ? '' : sprintf(lang('validate', 'confirmed'), $value);
    }

    public static function in($name, $value, $param)
    {
        $params = explode(',', $param);

        return empty($value) || (in_array($value, $params)) ? '' : sprintf(lang('validate', 'in'), $value, $param);
    }


    public static function file($name, $value, $param)
    {
        $result = FileMime::checkExtenstion($value, $param);

        return ($result['status'] == 1) ? '' : sprintf(lang('validate', 'file'), $result['ext']);
    }


}