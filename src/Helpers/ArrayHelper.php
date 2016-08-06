<?php
namespace Toda\Helpers;
class ArrayHelper
{
    public static function sort(&$array, $key, $reverse = false)
    {
        usort($array, function ($a, $b) use ($key, $reverse) {
            if ($reverse) {
                return $b[$key] - $a[$key];
            }
            return $a[$key] - $b[$key];
        });
    }

    public static function popMore(&$array, $count)
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            if (!empty($array)) {
                $result[] = array_pop($array);
            } else {
                return $result;
            }
        }
        return $result;
    }

    public static function filterKeyArrayObj($array, $key, $value)
    {
        foreach ($array as $k => $item) {

            if($item->$key == $value){
                return $k;
            }
        }
        return -1;
    }

    public static function filterKeyArray($array, $key, $value)
    {
        foreach ($array as $k => $item) {
            if($item[$key] == $value){
                return $k;
            }
        }
        return -1;
    }
}