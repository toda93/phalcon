<?php
namespace Toda\Helpers;
class TimeHelper
{
    public static function speed($callback)
    {
        if (is_callable($callback)) {
            $time_start = microtime(true);
            call_user_func($callback);
            echo number_format(round(microtime(true) - $time_start, 10), 10) . " Sec";
        }
    }

    public static function elapsed($time, $timestamp = false)
    {
        if ($timestamp) {
            $time = strtotime($time);
        }
        $now = time() - $time;
        $result = date('Y-m-d H:i:s', $time);
        if($now < 60){
            $result = 'Just now';
        } else if($now < 3600){
            $minutes = (int)($now / 60);
            $result = $minutes . ' minute' . ($minutes == 1 ? '': 's') . ' ago';
        } else if($now < 3600 * 24) {
            $hours = (int)($now / 3600);
            $result = $hours . ' hour' . ($hours == 1 ? '': 's') . ' ago';
        } else if($now < 3600 * 24 * 3) {
            $days = (int)($now / (3600 * 24));
            $result = $days . ' day' . ($days == 1 ? '': 's') . ' ago';
        }
        return $result;
    }

}