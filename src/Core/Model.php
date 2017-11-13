<?php
namespace Toda\Core;

\Phalcon\Mvc\Model::setup(['notNullValidations' => false]);

class Model extends \Phalcon\Mvc\Model
{
    protected $track_time = false;
    protected $track_user = false;

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

    public function disableTrack($time = false, $user = false)
    {
        $this->track_time = $time;
        $this->track_user = $user;
        return $this;
    }

    public function beforeCreate()
    {
        if ($this->track_time) {
            $this->created_at = $this->updated_at = time();
        }
        if ($this->track_user) {
            if (empty($this->getDI()->getSession()->get('auth'))) {
                $this->created_id = $this->updated_id = 1;
            } else {
                $this->created_id = $this->updated_id = $this->getDI()->getSession()->get('auth')->id;
            }
        }
    }

    public function beforeUpdate()
    {
        if ($this->track_time) {
            $this->updated_at = time();
        }
        if ($this->track_user) {
            if (empty($this->getDI()->getSession()->get('auth'))) {
                $this->updated_id = 1;
            } else {
                $this->updated_id = $this->getDI()->getSession()->get('auth')->id;
            }
        }
    }
}