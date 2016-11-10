<?php
namespace Toda\Html;

class Form {
    
    private $old;

    private $name;
    private $value;

    private $opts;
    private $html = '';

    public function __construct($old){
        $this->old = $old;
    }

    public function __toString(){

        $this->html = str_replace('{options}', self::buildOption($this->opts),$this->html);
        $this->html = str_replace('{value}', $this->buildValue(),$this->html);
        
        $this->value = '';
        $this->name = '';
        $this->opts = '';

        return $this->html;
    }

    protected static function buildOption(array $options){
        $option = '';
        foreach($options as $key=>$value){
            $option .= " $key='$value' ";
        }
        return $option;
    }

    protected function buildValue(){
        $name = ($this->name);
        return empty($this->old->$name) ? $this->value : $this->old->$name;
    }

    
    public static function begin($old, array $options = []){
        echo "<form" . self::buildOption($options) . ">";
        return new self($old);
    }

    public static function end(){
        echo '</form>';
    }

    public function field($name, $model = null){
        $this->name = $name;
        if(!empty($model)){
            $this->value = $model->$name;
        }
        return $this;
    }

    public function val($value){
        $this->value = $value;
        return $this;
    }
    
    public function hidden(array $options = []){
        $this->opts = $options;
        $this->html = "<input type='hidden' name='{$this->name}' {options} value='{value}'>";
        return $this;
    }

    public function email(array $options = []){
        $this->opts = $options;
        $this->html = "<input type='email' name='{$this->name}' {options} value='{value}'>";
        return $this;
    }

    public function password(array $options = []){
    $this->opts = $options;
    $this->html = "<input type='password' name='{$this->name}' {options}>";
    return $this;
}

    public function text(array $options = []){
        $this->opts = $options;
        $this->html = "<input type='text' name='{$this->name}' {options} value='{value}'>";
        return $this;
    }
    public function url(array $options = []){
        $this->opts = $options;
        $this->html = "<input type='url' name='{$this->name}' {options} value='{value}'>";
        return $this;
    }

    public function number(array $options = []){
        $this->opts = $options;
        $this->html = "<input type='number' name='{$this->name}' {options} value='{value}'>";
        return $this;
    }

    public function file(array $options = []){
        $this->opts = $options;
        $this->html = "<input type='file' name='{$this->name}' {options} value='{value}'>";
        return $this;
    }

    public function select(array $values , array $options = []){
        $this->opts = $options;
        $this->html = "<select name='{$this->name}' {options}>";

        $temp = $this->buildValue();

        foreach($values as $key=>$value){
            if ($temp == $key) {
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
        $this->opts = $options;
        $temp = $this->buildValue();

        $this->html = "";

        foreach ($values as $key => $value) {

            $this->html .= "<span class='checkbox-inline'><input type='checkbox' name='{$this->name}[]' value='$key'";

            if ($temp == $key) {
                $this->html .= ' checked';
            }
            $this->html .= "{options}> $value </span>";
        }
        return $this;
    }

    public function radioList(array $values, array $options = array())
    {
        $this->opts = $options;
        $temp = $this->buildValue();

        $this->html = "";

        foreach ($values as $key => $value) {

            $this->html .= "<span class='radio-inline'><input type='radio' name='{$this->name}' value='$key'";

            if ($temp == $key) {
                $this->html .= ' checked';
            }
            $this->html .= "{options}> $value </span>";
        }
        return $this;
    }

    public function checkbox($value, array $options = []){
        $this->opts = $options;

        $temp = $this->buildValue();

        $this->html = "<input type='checkbox' name='{$this->name}' {options} value='$value'";
        if ($temp == $value) {
            $this->html .= ' checked';
        }
        $this->html .= ">";

        return $this;
    }

    public function textarea(array $options = []){
        $this->opts = $options;
        $this->html = "<textarea name='{$this->name}' {options}>{value}</textarea>";
        return $this;
    }
    
    public function captcha(array $options = []){
        $this->name = 'captcha';
        $this->opts = $options;
        $this->html = "<div class='g-recaptcha' data-sitekey='" . page('recaptcha_key') ."' {options}></div>";
        return $this;
    }
}