<?php
namespace Toda\Helper;
class DirectoryHelper
{
    public static function create_folder($path)
    {
        if (!is_dir($path)) mkdir($path);
    }

    public static function remove_folder($path)
    {
        if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($path . "/" . $object) == "dir")
                        rrmdir($path . "/" . $object);
                    else unlink($path . "/" . $object);
                }
            }
            reset($objects);
            rmdir($path);
        }
    }
}