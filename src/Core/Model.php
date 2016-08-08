<?php
namespace Toda\Core;

\Phalcon\Mvc\Model::setup(['notNullValidations' => false]);

class Model extends \Phalcon\Mvc\Model
{
    protected $track = true;

    public function initialize()
    {

    }
    public function setConnection($name)
    {
        $this->setConnectionService($name);
        $this->setSchema($name);
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
        if ($this->track) {
            $this->created_at = $this->updated_at = time();
            $this->created_id = $this->updated_id = $this->getDI()->getSession()->get('auth')['id'];
        }
    }

    public function beforeUpdate()
    {
        if ($this->track) {
            $this->updated_at = time();
            $this->updated_id = $this->getDI()->getSession()->get('auth')['id'];
        }
    }
}