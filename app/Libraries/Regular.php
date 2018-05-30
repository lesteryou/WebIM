<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/4/28 11:14
 * Desc:
 */

namespace App\Libraries;


class Regular
{
    /**
     * User\InfoController@Index -> \App\Http\Controllers\User\InfoController:Index
     * @param $callback
     * @return string
     */
    public static function RouteControllerReplace($callback)
    {
        $preg = '/^\w+@\w+$/';
        if (preg_match($preg, $callback)) {
            $arr = explode('@', $callback);
            if (!empty($arr) && count($arr) == 2) {
                return '\App\Http\Controllers\\' . $arr[0] . ':' . $arr[1];
            }
        }
        return $callback;
    }
}