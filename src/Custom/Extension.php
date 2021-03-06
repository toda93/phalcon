<?php
namespace Toda\Custom;

class Extension
{
    /**
     * This method is called on any attempt to compile a function call
     */

    public function compileFunction($name, $arguments)
    {
        if (function_exists($name)) {
            return $name . '(' . $arguments . ')';
        } else {
            if ($name == 'pagination') {
                return '\Toda\Html\Pagination::render(' . $arguments . ')';
            }
            if ($name == 'form_begin') {
                return '\Toda\Html\Form::begin(' . $arguments . ')';
            }
            if ($name == 'form_end') {
                return '\Toda\Html\Form::end()';
            }
            if ($name == 'elapsed') {
                return '\Toda\Helper\TimeHelper::elapsed(' . $arguments . ')';
            }
            if ($name == 'check_role') {
                return '\Toda\Helper\RoleHelper::check(' . $arguments . ')';
            }
        }
    }
}