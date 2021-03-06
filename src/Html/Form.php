<?php
namespace Toda\Html;

class Form extends \Phalcon\Mvc\User\Plugin
{

    private $old;

    private $name;
    private $value;

    private $opts;
    private $html = '';

    public function __construct()
    {
        $this->old = null;
        if ($this->getDI()->getSession()->has('old')) {
            $this->old = $this->getDI()->getSession()->get('old');
            $this->getDI()->getSession()->remove('old');
        }
    }

    public function __toString()
    {

        $this->html = str_replace('{options}', self::buildOption($this->opts), $this->html);
        $this->html = str_replace('{value}', $this->buildValue(), $this->html);

        $this->value = '';
        $this->name = '';
        $this->opts = '';

        return $this->html;
    }

    protected static function buildOption(array $options)
    {
        $option = '';
        foreach ($options as $key => $value) {

            if (is_null($value)) {
                $option .= " $key";
            } else {
                $option .= " $key='$value'";
            }

        }
        return $option;
    }

    protected function buildValue()
    {
        $name = ($this->name);
        return empty($this->old[$name]) ? $this->value : $this->old[$name];
    }

    public static function begin(array $options = [])
    {
        echo "<form" . self::buildOption($options) . ">";

        if (!empty($options['method'])) {
            echo "<input type='hidden' name='_csrf' value='" . \Phalcon\DI::getDefault()->getSession()->get('csrf_token') . "'>";
        }
        return new self();
    }

    public static function end()
    {
        echo '</form>';
    }

    public function field($name, $model = null)
    {
        $this->name = $name;
        if (!empty($model)) {
            $this->value = $model->$name;
        }
        return $this;
    }

    public function val($value)
    {
        $this->value = $value;
        return $this;
    }

    public function hidden(array $options = [])
    {
        $this->opts = $options;
        $this->html = "<input type='hidden' name='{$this->name}'{options} value='{value}'>";
        return $this;
    }

    public function email(array $options = [])
    {
        $this->opts = $options;
        $this->html = "<input type='email' name='{$this->name}'{options} value='{value}'>";
        return $this;
    }

    public function password(array $options = [])
    {
        $this->opts = $options;
        $this->html = "<input type='password' name='{$this->name}'{options} value='{value}'>";
        return $this;
    }

    public function text(array $options = [])
    {
        $this->opts = $options;
        $this->html = "<input type='text' name='{$this->name}'{options} value='{value}'>";
        return $this;
    }

    public function url(array $options = [])
    {
        $this->opts = $options;
        $this->html = "<input type='url' name='{$this->name}'{options} value='{value}'>";
        return $this;
    }

    public function number(array $options = [])
    {
        $this->opts = $options;
        $this->html = "<input type='number' name='{$this->name}'{options} value='{value}'>";
        return $this;
    }

    public function file(array $options = [])
    {
        $this->opts = $options;
        $this->html = "<input type='file' name='{$this->name}'{options}>";
        return $this;
    }

    public function image(array $options = [])
    {
        $this->opts = $options;
        $this->html = "<input type='file' name='{$this->name}'{options} data-url='{value}'>";
        return $this;
    }

    public function select(array $values, array $options = [])
    {
        $this->opts = $options;
        $this->html = "<select name='{$this->name}'{options}>";

        $temp = $this->buildValue();


        foreach ($values as $key => $value) {
            if ($temp === (string)$key) {
                $this->html .= "<option value='$key' selected>$value</option>";
            } else {
                $this->html .= "<option value='$key'>$value</option>";
            }
        }
        $this->html .= "</select>";
        return $this;
    }

    public function checkboxList(array $values, array $options = array())
    {
        $parent_class = 'checkbox-inline';
        if (!empty($options['parent-class'])) {
            $parent_class = $options['parent-class'];
            unset($options['parent-class']);
        }

        $temp = $this->buildValue();

        if (!is_array($temp)) {
            if (!empty($options['checkbox-type']) && $options['checkbox-type'] == 'json') {
                unset($options['checkbox-type']);

                $temp = json_decode($temp, true);
            } else {

                $temp = explode(',', $temp);
            }
        }

        $this->opts = $options;

        $this->html = "";

        foreach ($values as $key => $value) {

            $this->html .= "<div class='$parent_class'><input type='checkbox' name='{$this->name}[]' value='$key'";

            if (in_array($key, $temp)) {
                $this->html .= ' checked';
            }
            $this->html .= "{options}> $value </div>";
        }
        return $this;
    }

    public function radioList(array $values, array $options = array())
    {
        $parent_class = 'radio-inline';
        if (!empty($options['parent-class'])) {
            $parent_class = $options['parent-class'];
            unset($options['parent-class']);
        }

        $this->opts = $options;
        $temp = $this->buildValue();

        $this->html = "";

        foreach ($values as $key => $value) {

            $this->html .= "<div class='$parent_class'><input type='radio' name='{$this->name}' value='$key'";

            if ($temp == $key) {
                $this->html .= ' checked';
            }
            $this->html .= "{options}> $value </div>";
        }
        return $this;
    }

    public function checkbox($value, array $options = [])
    {
        $this->opts = $options;

        $temp = $this->buildValue();

        $this->html = "<input type='checkbox' name='{$this->name}'{options} value='$value'";

        if ((string)$temp === (string)$value) {
            $this->html .= ' checked';
        }
        $this->html .= ">";

        return $this;
    }

    public function textarea(array $options = [])
    {
        $this->opts = $options;
        $this->html = "<textarea name='{$this->name}'{options}>{value}</textarea>";
        return $this;
    }

    public function recaptcha($recaptcha_key, array $options = [])
    {
        $this->name = 'captcha';
        $this->opts = $options;
        $this->html = "<script src='https://www.google.com/recaptcha/api.js'></script><div class='g-recaptcha' data-sitekey='$recaptcha_key' {options}></div>";
        return $this;
    }
}