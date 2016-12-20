<?php
namespace Toda\Cache;

class CacheManager
{
    private $cache;
    private $path;

    public function __construct($cache, $path)
    {
        $this->cache = $cache;
        $this->path =  $path;
    }

    public function remember($name, $time = 60, $callback = false, $use = true)
    {
        $result = $this->get($name, $time);

        if (empty($result)) {
            if (is_callable($callback)) {
                $result = call_user_func($callback);

            } else {
                $result = $callback;
            }
            if($use && !is_developer()){
                $this->set($name, $result, $time);
            }
        }
        return $result;
    }

    public function clear($name){
        $this->cache->delete($name);
    }

    protected function getDirectory($name){

        $direct = strlen($name);

        $path = $this->path . $direct;

        if(!file_exists($path)){
            mkdir($path, 0755, true);
        }
        return $direct;
    }

    public function get($name, $time){

        $name = $this->getDirectory($name) . '/' . $name;

        return $this->cache->get($name, $time);
    }

    public function set($name, $content, $time){

        $name = $this->getDirectory($name) . '/' . $name;

        $this->cache->save($name, $content, $time);
    }
}