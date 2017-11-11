<?php
namespace Toda\Cache;

class MemcachedManager
{
    private $cache;
    private $debug;


    public function __construct($cache, $debug = false)
    {
        $this->cache = $cache;
        $this->debug = $debug;
    }

    public function remember($name, $time = 60, $callback = false, $use = true)
    {
        $result = $use && !$this->debug ? $this->get($name) : null;

        if (empty($result)) {
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
        $this->cache->delete($name);
    }

    public function get($name)
    {
        return $this->cache->get($name);
    }

    public function set($name, $content, $time)
    {
        if (empty($this->cache->get($name))) {
            $this->cache->add($name, $content, $time);
        } else {
            $this->cache->replace($name, $content, $time);
        }
    }
}