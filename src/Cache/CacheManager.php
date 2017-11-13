<?php
namespace Toda\Cache;

class CacheManager
{
    private $cache;
    private $path;
    private $debug;

    public function __construct($cache, $path, $debug = false)
    {
        $this->cache = $cache;
        $this->path = $path;
        $this->debug = $debug;

    }

    public function remember($name, $time = 60, $callback = false, $use = true)
    {
        $result = $use && !$this->debug ? $this->get($name, $time) : null;

        if (empty($result) || $this->debug) {
            if (is_callable($callback)) {
                $result = call_user_func($callback);

            } else {
                $result = $callback;
            }
            if ($use) {
                $this->set($name, $result, $time);
            }
        }
        return $result;
    }

    public function clear($name)
    {
        $name = $this->getDirectory($name) . '/' . $name;

        $this->cache->delete($name);
    }

    protected function getDirectory($name)
    {

        $direct = strlen($name);

        $path = $this->path . $direct;

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        return $direct;
    }

    public function get($name, $time)
    {

        $name = $this->getDirectory($name) . '/' . $name;

        return $this->cache->get($name, $time);
    }

    public function set($name, $content, $time)
    {

        $name = $this->getDirectory($name) . '/' . $name;

        $this->cache->save($name, $content, $time);
    }
}