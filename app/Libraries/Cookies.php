<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/21 14:42
 * Desc:
 */

namespace App\Libraries;


use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Slim\Http\Request;
use Slim\Http\Response;

class Cookies
{
    protected static $request;
    protected static $response;
    public static $instance;

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function req(Request $request)
    {
        self::$request = $request;
        return self::getInstance();
    }

    public static function res(Response $response)
    {
        self::$response = $response;
        return self::getInstance();
    }

    public function set($name, $values, $expires = 86400)
    {
        self::$response = FigResponseCookies::set(self::$response, SetCookie::create($name)
            ->withValue($values)
            ->withExpires(date(DATE_COOKIE,time() + $expires))
        );
        return self::$response;
    }
}