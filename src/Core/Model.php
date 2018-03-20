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

    public function disableTrack($time = false, $user = false)
    {
        $this->track_time = $time;
        $this->track_user = $user;
        return $this;
    }

    public function beforeCreate()
    {

        if (property_exists($this, 'created_at')) {
            $this->created_at = time();
        }
        if (property_exists($this, 'created_id')) {
            if (empty($this->getDI()->getSession()->get('auth'))) {
                $this->created_id = 1;
            } else {
                $this->created_id = $this->getDI()->getSession()->get('auth')->id;
            }
        }

        $this->beforeUpdate();
    }

    public function beforeUpdate()
    {
        if (property_exists($this, 'updated_at') && $this->status != -1) {
            $this->updated_at = time();
        }
        if (property_exists($this, 'updated_id') && $this->status != -1) {
            if (empty($this->getDI()->getSession()->get('auth'))) {
                $this->updated_id = 1;
            } else {
                $this->updated_id = $this->getDI()->getSession()->get('auth')->id;
            }
        }
    }

    public function delete($force = false)
    {
        if (!$force && (property_exists($this, 'status') && property_exists($this, 'deleted_at') && property_exists($this, 'deleted_id'))) {
            $this->status = -1;
            $this->deleted_at = time();
            $this->deleted_id = $this->getDI()->getSession()->get('auth')->id;
            return $this->save();
        }
        return parent::delete();
    }
}