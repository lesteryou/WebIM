<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/7/5 13:29
 * Desc:
 */

namespace Server;


class ObjectPool
{
    private $instances = [];

    public function get($key)
    {
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        } else {
            $item = $this->make($key);
            $this->instances[$key] = $item;
            return $item;
        }
    }

    public function add($object, $key)
    {
        $this->instances[$key] = $object;
    }

    public function make($key)
    {
        if ($key == 'mysql') {

        } elseif ($key == 'socket') {

        }
        return '';
    }
}