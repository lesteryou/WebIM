<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/6/11 1:04
 * Desc:
 */

function onlineIntToString($status)
{
    switch ($status) {
        case 1:
            return 'online';
            break;
        case 2:
            return 'hide';
            break;
        case 0:
        default:
            return 'offline';
            break;
    }
}

function onlineStringToInt($statusString)
{
    switch ($statusString) {
        case 'online':
            return 1;
            break;
        case 'hide':
            return 2;
            break;
        case 'offline':
        default:
            return 0;
            break;
    }
}