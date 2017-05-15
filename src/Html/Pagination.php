<?php
namespace Toda\Html;

class Pagination
{
    private static $options = [
        'current' => 1,
        'total' => 1,
        'limit' => 5,
        'prev-next' => false,
        'prev' => '<',
        'next' => '>',
        'first-last' => false,
        'first' => '<<',
        'last' => '>>',
        'class' => 'pagination',
        'id' => '',
        'active-class' => 'active',
    ];

    public static function render(array $options = [])
    {

        if (empty($options['url'])) {
            $url = $_SERVER['REQUEST_URI'];
        } else {
            $url = $options['url'];
        }

        $url = preg_replace('/&?page=([^&]+)/', '', $url);

        if (preg_match('/\?/', $url)) {
            if(preg_match('/\?$/', $url)){
                $url = $url . 'page=';
            } else {
                $url = $url . '&page=';
            }

        } else {
            $url = $url . '?page=';
        }

        $options = array_merge(self::$options, $options);

        if ($options['total'] <= 1) {
            return '';
        }

        $id = empty($options['id']) ? '' : "id='{$options['id']}'";
        $html = '';

        $pos = (floor($options['current'] / ($options['limit'])) * $options['limit']);
        $options['current'] - $pos == 0 && $pos--;
        $options['current'] - $pos == ($options['limit'] - 1) && $pos++;
        $pos < 1 && $pos = 1;

        $max_pos = $pos + $options['limit'] - 1;
        if ($max_pos > $options['total']) {
            $pos = $pos - ($max_pos - $options['total']);
            $max_pos = $options['total'];
        }
        $pos < 1 && $pos = 1;

        for ($i = $pos; $i <= $max_pos; $i++) {
            $add_class = $i == $options['current'] ? " class={$options['active-class']}" : "";
            $html .= "<li $add_class><a href='{$url}{$i}' data-page='{$i}'>$i</a></li>";

        }

        if ($options['prev-next']) {
            if ($options['current'] > 1) {
                $i = $options['current'] - 1;
                $html = "<li class='previous'><a href='{$url}{$i}' data-page='{$i}'>{$options['prev']}</a></li>" . $html;
            }
            if ($options['current'] < $options['total']) {
                $i = $options['current'] + 1;
                $html .= "<li class='next'><a href='{$url}{$i}' data-page='{$i}'>{$options['next']}</a></li>";
            }
        }

        if ($options['first-last']) {
            if ($options['current'] > 1) {
                $html = "<li class='first'><a href='{$url}1' data-page='1'>{$options['first']}</a></li>" . $html;
            }
            if ($options['current'] < $options['total']) {
                $html .= "<li class='last'><a href='{$url}{$options['total']}' data-page='1'>{$options['last']}</a></li>";
            }
        }

        $html = "<ul class='{$options['class']}' $id>$html</ul>";

        return $html;
    }


}