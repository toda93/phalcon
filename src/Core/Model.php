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

    protected function  setWriteConnection($name)
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
            $this->created_at = time();
        }
        if (property_exists($this, 'created_id')) {

            $session = $this->getDI()->getSession();
            if ($session->status() == $session::SESSION_ACTIVE && !empty($session->get('auth'))) {
                $this->created_id = $this->getDI()->getSession()->get('auth')->id;
            }
        }

        $this->beforeUpdate();
    }

    public function beforeUpdate()
    {

        if (property_exists($this, 'updated_at')) {
            $this->updated_at = time();
        }

        if (property_exists($this, 'updated_id')) {
            $this->updated_id = 1;

            $session = $this->getDI()->getSession();
            if ($session->status() == $session::SESSION_ACTIVE && !empty($session->get('auth'))) {
                $this->updated_id = $this->getDI()->getSession()->get('auth')->id;
            }
        }
    }
}