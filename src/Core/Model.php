<?php

namespace Toda\Core;

\Phalcon\Mvc\Model::setup(['notNullValidations' => false]);

class Model extends \Phalcon\Mvc\Model
{
    protected function setConnection($name)
    {
        $this->setConnectionService($name);
        $this->setSchema($name);
    }

    protected function setReadConnection($name)
    {
        $this->setReadConnectionService($name);
    }

    protected function setWriteConnection($name)
    {
        $this->setWriteConnectionService($name);
    }

    public static function __callStatic($method, $parameters)
    {
        $builder = new QueryBuilder([
            'models' => static::class
        ]);
        return call_user_func_array([$builder, $method], $parameters);
    }

    public function beforeCreate()
    {

        if (property_exists($this, 'created_at')) {
            $this->updated_at = $this->created_at = time();
        }
        if (property_exists($this, 'created_id')) {
            $this->updated_id = $this->created_id = 1;

            if ($this->getDI()->has('session') && !empty($this->getDI()->getSession()->get('auth'))) {
                $this->updated_id = $this->created_id = $this->getDI()->getSession()->get('auth')['user']->id;
            }
        }
    }

    public function beforeUpdate()
    {
        if (property_exists($this, 'updated_at')) {
            $this->updated_at = time();
        }

        if (property_exists($this, 'updated_id')) {
            $this->updated_id = 1;

            if ($this->getDI()->has('session') && !empty($this->getDI()->getSession()->get('auth'))) {
                $this->updated_id = $this->getDI()->getSession()->get('auth')['user']->id;
            }
        }
    }
}