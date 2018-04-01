<?php
namespace Toda\Helper;

use Intervention\Image\ImageManagerStatic as Image;


class ImageHelper
{
    public static function make($img)
    {
        Image::configure(array('driver' => 'imagick'));

        return Image::make($img);

    }
}